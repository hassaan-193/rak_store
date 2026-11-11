<?php

namespace App\Services\Integration;

use App\Models\IncomingMaterial;
use App\Models\IncomingMaterialItem;
use App\Models\PoReceiptEntry;
use App\Models\PortalOrder;
use App\Models\PortalOrderLine;
use App\Models\Supplier;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Records and voids PO deliveries. Every call is ONE DB transaction that locks the order,
 * moves stock where the rules say so, bumps event_seq and writes the outbound portal event
 * into the outbox (integration_messages). Delivery to the portal is tried after commit;
 * `php artisan portal:retry` resends anything that failed.
 */
class PoReceiptService
{
    const EPS = 0.0005;

    /** True when the last recordEntry() call returned an existing entry (same submission_uuid). */
    public $lastReplayed = false;

    /**
     * @param array $data portal_order_line_id, type, quantity, entry_date, vendor_dn_number,
     *                    site_receiver_name, source_stock_item_id, issue_destination, category,
     *                    store_location, stock_scope (general|project), submission_uuid, note
     * @throws DomainException with a message safe to show the user
     */
    public function recordEntry(PortalOrder $order, array $data, ?UploadedFile $file, int $userId): PoReceiptEntry
    {
        $storedPath = null;
        $outboxId = null;
        $this->lastReplayed = false;

        try {
            $entry = DB::transaction(function () use ($order, $data, $file, $userId, &$storedPath, &$outboxId) {
                $locked = PortalOrder::lockForUpdate()->findOrFail($order->id);

                // Retry of the same submission: return what was saved, change nothing.
                $uuid = trim((string) ($data['submission_uuid'] ?? '')) ?: null;
                if ($uuid) {
                    $existing = PoReceiptEntry::where('portal_order_id', $locked->id)->where('submission_uuid', $uuid)->first();
                    if ($existing) {
                        $this->lastReplayed = true;

                        return $existing;
                    }
                }

                if ($locked->isVoided()) {
                    throw new DomainException('This order is voided and archived; new entries cannot be recorded.');
                }
                if ($locked->isCancelled()) {
                    throw new DomainException('This order is cancelled; new entries cannot be recorded.');
                }
                if ($locked->legacy_flow) {
                    throw new DomainException('This order was completed under the old dispatch flow; new entries cannot be recorded.');
                }

                $line = PortalOrderLine::where('portal_order_id', $locked->id)->lockForUpdate()->find($data['portal_order_line_id']);
                if (!$line) {
                    throw new DomainException('The selected item does not belong to this order.');
                }

                $type = $data['type'];
                $qty = round((float) $data['quantity'], 3);
                if ($qty <= 0) {
                    throw new DomainException('Quantity must be greater than zero.');
                }

                // Vendor deliveries can never exceed what the vendor still owes (checked under the lock).
                if ($type !== 'from_stock') {
                    $locked->unsetRelation('entries');
                    $row = collect($locked->breakdown())->firstWhere('line_id', $line->id);
                    $due = $row ? (float) $row['vendor_due'] : 0.0;
                    if ($qty > $due + 0.0005) {
                        throw new DomainException('Quantity cannot exceed the vendor balance for this item (' . rtrim(rtrim(number_format($due, 3, '.', ''), '0'), '.') . ' still due).');
                    }
                }

                $fields = [
                    'portal_order_id' => $locked->id,
                    'portal_order_line_id' => $line->id,
                    'type' => $type,
                    'quantity' => $qty,
                    'entry_date' => $data['entry_date'],
                    'vendor_dn_number' => $data['vendor_dn_number'] ?? null,
                    'note' => $data['note'] ?? null,
                    'recorded_by' => $userId,
                    'submission_uuid' => $uuid,
                ];

                if ($type === 'store_receipt') {
                    $this->requireText($fields['vendor_dn_number'], 'Vendor DN number is required.');
                    $this->requireText($data['store_location'] ?? null, 'Warehouse location is required.');
                    $fields['stock_item_id'] = $this->addToStock($locked, $line, $qty, $data, $userId)->id;
                } elseif ($type === 'direct_to_site') {
                    $this->requireText($fields['vendor_dn_number'], 'Vendor DN number is required.');
                    $this->requireText($data['site_receiver_name'] ?? null, 'Site receiver name is required.');
                    if (!$file) {
                        throw new DomainException('A photo or PDF of the signed DN is required.');
                    }
                    $fields['site_receiver_name'] = trim($data['site_receiver_name']);
                    $storedPath = Storage::putFileAs(
                        'po-delivery-notes',
                        $file,
                        Str::random(40) . '.' . strtolower($file->extension())
                    );
                    $fields['dn_file_path'] = $storedPath;
                } elseif ($type === 'from_stock') {
                    $this->requireText($data['issue_destination'] ?? null, 'Stock destination is required.');
                    $source = $this->eligibleStockQuery($locked, $line)->lockForUpdate()->find($data['source_stock_item_id'] ?? 0);
                    if (!$source) {
                        throw new DomainException('That stock row is not eligible for this item (wrong item/unit/project, already counted as this PO\'s receipt, empty, or in the recycle bin).');
                    }

                    // Same transaction, after the locks: how much of the line is still uncovered.
                    $t = PoReceiptEntry::where('portal_order_id', $locked->id)->where('portal_order_line_id', $line->id)
                        ->whereNull('voided_at')->selectRaw('type, SUM(quantity) as q')->groupBy('type')->pluck('q', 'type');
                    $covered = min((float) $line->quantity_ordered, (float) ($t['store_receipt'] ?? 0) + (float) ($t['direct_to_site'] ?? 0) + (float) ($t['from_stock'] ?? 0));
                    $toCover = round((float) $line->quantity_ordered - $covered, 3);
                    $available = round((float) $source->quantity, 3);
                    $max = round(min($toCover, $available), 3);

                    if ($toCover <= self::EPS) {
                        throw new DomainException('This item is already fully covered; nothing left to issue from stock.');
                    }
                    if ($qty > $max + self::EPS) {
                        throw new DomainException('You can issue at most ' . $max . ' ' . $source->unit . ' (still to cover ' . $toCover . ', available in that row ' . $available . ').');
                    }

                    $before = (float) $source->quantity;
                    $source->quantity = round($before - $qty, 2);
                    $source->save();

                    $parent = $source->incomingMaterial;
                    $fields['source_stock_item_id'] = $source->id;
                    $fields['stock_issue_details'] = [
                        'item' => $source->item_name,
                        'category' => $source->category,
                        'unit' => $source->unit,
                        'quantity' => $qty,
                        'issue_date' => (string) $data['entry_date'],
                        'source_stock_item_id' => $source->id,
                        'original_dn' => optional($parent)->dn_number,
                        'warehouse_location' => optional($parent)->store_location,
                        'source_scope' => optional($parent)->project_type === 'project' ? 'Project: ' . optional(optional($parent)->project)->name : 'General stock',
                        'destination' => trim($data['issue_destination']),
                        'storekeeper' => optional(\Auth::user())->name,
                        'balance_before' => $before,
                        'balance_after' => (float) $source->quantity,
                    ];
                } else {
                    throw new DomainException('Unknown entry type.');
                }

                $entry = PoReceiptEntry::create($fields);
                $outboxId = $this->bumpAndQueue($locked);

                return $entry;
            });
        } catch (\Throwable $e) {
            // The transaction rolled back: do not leave the uploaded file behind.
            if ($storedPath) {
                Storage::delete($storedPath);
            }
            throw $e;
        }

        $this->deliverQuietly($outboxId);

        return $entry;
    }

    /**
     * Stock rows this PO may be supplied from, for one line: same item and unit, an active
     * Incoming parent, General stock or the PO's mapped Store project, and NOT stock that this PO's
     * own vendor receipts created (including that stock after a transfer to General, which keeps
     * the PO link or the same DN number).
     */
    public function eligibleStockQuery(PortalOrder $order, PortalOrderLine $line)
    {
        $receipts = PoReceiptEntry::where('portal_order_id', $order->id)->where('type', 'store_receipt')->get(['stock_item_id', 'vendor_dn_number']);
        $ownRowIds = $receipts->pluck('stock_item_id')->filter()->all();
        $ownDns = $receipts->pluck('vendor_dn_number')->filter()->map(function ($d) {
            return mb_strtolower(trim($d));
        })->unique()->values()->all();

        $q = IncomingMaterialItem::query()
            ->where('quantity', '>', 0)
            ->whereRaw('LOWER(TRIM(item_name)) = ?', [mb_strtolower(trim($line->material_name))])
            ->where('unit', $line->unit ?: 'NOS')
            ->whereHas('incomingMaterial', function ($p) use ($order, $ownDns) {
                $p->where(function ($w) use ($order) {
                    $w->where('project_type', 'general');
                    if ($order->project_id) {
                        $w->orWhere(function ($x) use ($order) {
                            $x->where('project_type', 'project')->where('project_id', $order->project_id);
                        });
                    }
                });
                $p->where(function ($w) use ($order) {
                    $w->whereNull('portal_order_id')->orWhere('portal_order_id', '!=', $order->id);
                });
                if ($ownDns) {
                    $p->where(function ($w) use ($ownDns) {
                        $w->whereNull('dn_number')->orWhereNotIn(DB::raw('LOWER(TRIM(dn_number))'), $ownDns);
                    });
                }
            });
        if ($ownRowIds) {
            $q->whereNotIn('id', $ownRowIds);
        }

        return $q;
    }

    /** Eligible rows with their parent, oldest first (for the screen). */
    public function eligibleStock(PortalOrder $order, PortalOrderLine $line)
    {
        return $this->eligibleStockQuery($order, $line)->with('incomingMaterial.project')->orderBy('id')->get();
    }

    public function voidEntry(PoReceiptEntry $entry, string $reason, int $userId): PoReceiptEntry
    {
        $outboxId = null;

        $entry = DB::transaction(function () use ($entry, $reason, $userId, &$outboxId) {
            $locked = PortalOrder::lockForUpdate()->findOrFail($entry->portal_order_id);
            $e = PoReceiptEntry::lockForUpdate()->findOrFail($entry->id);

            if ($locked->isVoided()) {
                throw new DomainException('This order is voided and archived; its history cannot be changed.');
            }
            if ($e->voided_at) {
                throw new DomainException('This entry is already voided.');
            }
            if (trim($reason) === '') {
                throw new DomainException('A reason is required to void an entry.');
            }

            if ($e->type === 'store_receipt') {
                $row = $e->stock_item_id ? IncomingMaterialItem::withTrashed()->lockForUpdate()->find($e->stock_item_id) : null;
                if (!$row || $row->trashed()) {
                    throw new DomainException('The stock row from this receipt is in the recycle bin or missing; it cannot be voided now.');
                }
                if ((float) $row->quantity + self::EPS < (float) $e->quantity) {
                    throw new DomainException('Cannot void: stock was already issued (only ' . (float) $row->quantity . ' ' . $row->unit . ' of ' . $row->item_name . ' left, receipt was ' . (float) $e->quantity . ').');
                }
                $row->quantity = round((float) $row->quantity - (float) $e->quantity, 2);
                $row->save();
            } elseif ($e->type === 'from_stock') {
                // Restore exactly what was issued, to the original row, once (the voided_at guard above).
                $row = $e->source_stock_item_id ? IncomingMaterialItem::withTrashed()->lockForUpdate()->find($e->source_stock_item_id) : null;
                if (!$row || $row->trashed()) {
                    throw new DomainException('The stock row this was supplied from is in the recycle bin or missing; restore it first.');
                }
                $before = (float) $row->quantity;
                $row->quantity = round($before + (float) $e->quantity, 2);
                $row->save();

                $d = $e->stock_issue_details ?: [];
                $d['reversal'] = [
                    'restored_quantity' => (float) $e->quantity,
                    'restored_at' => now()->toDateTimeString(),
                    'balance_before' => $before,
                    'balance_after' => (float) $row->quantity,
                    'by' => optional(\Auth::user())->name,
                ];
                $e->stock_issue_details = $d;
            }
            // direct_to_site never touched stock.

            $e->forceFill([
                'voided_at' => now(),
                'void_reason' => trim($reason),
                'voided_by' => $userId,
            ])->save();

            $outboxId = $this->bumpAndQueue($locked);

            return $e;
        });

        $this->deliverQuietly($outboxId);

        return $entry;
    }

    /**
     * Whole-PO void confirmed by the portal. Archives the order (read-only); completed stock
     * movements and the delivery history are left exactly as they are. Idempotent. If the PO
     * was never sent, a voided stub is created so a late send is ignored.
     */
    public function voidOrder(int $portalPoId, string $reason, ?string $byName, $at = null): PortalOrder
    {
        return DB::transaction(function () use ($portalPoId, $reason, $byName, $at) {
            $order = PortalOrder::lockForUpdate()->where('portal_po_id', $portalPoId)->first();
            if (!$order) {
                $order = PortalOrder::create(['portal_po_id' => $portalPoId, 'status' => 'voided', 'items' => []]);
            }
            if (!$order->isVoided()) {
                $order->forceFill([
                    'status' => 'voided',
                    'voided_at' => $at ? Carbon::parse($at) : now(),
                    'void_reason' => $reason,
                    'voided_by_name' => $byName,
                ])->save();
            }

            return $order;
        });
    }

    /**
     * Create or refresh the order lines from the items the portal sent. Lines that have
     * entries are never removed. Returns true when anything changed.
     */
    public function syncLines(PortalOrder $order, array $items): bool
    {
        $wanted = [];
        foreach ($items as $i) {
            $key = PortalOrderLine::keyFor($i['material_name'], $i['item_code'] ?? null);
            if (isset($wanted[$key])) {
                $wanted[$key]['quantity'] += (float) ($i['quantity'] ?? 0);
            } else {
                $wanted[$key] = [
                    'material_name' => trim($i['material_name']),
                    'item_code' => ($i['item_code'] ?? null) ?: null,
                    'unit' => ($i['unit'] ?? null) ?: null,
                    'quantity' => (float) ($i['quantity'] ?? 0),
                ];
            }
        }

        $changed = false;
        $existing = PortalOrderLine::where('portal_order_id', $order->id)->get()->keyBy(function ($l) {
            return PortalOrderLine::keyFor($l->material_name, $l->item_code);
        });

        foreach ($wanted as $key => $w) {
            $line = $existing->get($key);
            if (!$line) {
                PortalOrderLine::create([
                    'portal_order_id' => $order->id,
                    'material_name' => $w['material_name'],
                    'item_code' => $w['item_code'],
                    'unit' => $w['unit'],
                    'quantity_ordered' => $w['quantity'],
                ]);
                $changed = true;
            } else {
                $line->fill(['unit' => $w['unit'], 'quantity_ordered' => $w['quantity']]);
                if ($line->isDirty()) {
                    $line->save();
                    $changed = true;
                }
            }
        }

        foreach ($existing as $key => $line) {
            if (!isset($wanted[$key]) && !$line->entries()->exists()) {
                $line->delete();
                $changed = true;
            }
        }

        return $changed;
    }

    /** Bump the sequence and queue the event for the current state. Call inside the transaction. */
    public function bumpAndQueue(PortalOrder $locked): ?int
    {
        $locked->event_seq = (int) $locked->event_seq + 1;
        $locked->save();

        return PortalClient::enqueue('purchase_order.updated', $locked->toPortalArray());
    }

    public function deliverQuietly(?int $outboxId): void
    {
        if (!$outboxId) {
            return;
        }
        try {
            PortalClient::deliver($outboxId);
        } catch (\Throwable $e) {
            // stays pending in the outbox; portal:retry will resend
        }
    }

    /** Each vendor receipt becomes its own Incoming Materials receipt (real vendor DN) with its own stock row. */
    protected function addToStock(PortalOrder $order, PortalOrderLine $line, float $qty, array $data, int $userId): IncomingMaterialItem
    {
        $category = trim($data['category'] ?? '') ?: 'General';
        $unit = $line->unit ?: 'NOS';
        $name = trim($line->material_name);

        $scope = ($data['stock_scope'] ?? 'general') === 'project' ? 'project' : 'general';
        if ($scope === 'project' && !$order->project_id) {
            throw new DomainException('This order has no matching Store project; choose General stock.');
        }

        $supplier = $order->supplier_name
            ? Supplier::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($order->supplier_name))])->first()
            : null;

        $header = new IncomingMaterial([
            'date_time' => Carbon::parse($data['entry_date'])->setTimeFrom(Carbon::now()),
            'source_type' => 'supplier',
            'supplier_id' => optional($supplier)->id,
            'project_type' => $scope,
            'project_id' => $scope === 'project' ? $order->project_id : null,
            'engineer_name' => optional(\Auth::user())->name ?: 'PO receiving',
            'store_location' => trim($data['store_location']),
            'dn_number' => trim($data['vendor_dn_number']),
        ]);
        $header->portal_order_id = $order->id;
        $header->save();

        return $header->items()->create([
            'item_name' => $name,
            'category' => $category,
            'brand' => null,
            'unit' => $unit,
            'quantity' => round($qty, 2),
        ]);
    }

    protected function requireText($value, string $message): void
    {
        if ($value === null || trim((string) $value) === '') {
            throw new DomainException($message);
        }
    }
}
