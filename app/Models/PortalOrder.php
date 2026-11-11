<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

class PortalOrder extends Model
{
    // Status is computed from the delivery entries (see statusKey()); the column only
    // tells "received" (active) from "cancelled".
    public const STATUS_KEYS = ['not_received', 'partially_received', 'fully_covered', 'fully_received', 'legacy_flow', 'cancelled', 'voided'];

    public const STATUS_LABELS = [
        'not_received' => 'Not received',
        'partially_received' => 'Partially received',
        'fully_covered' => 'Fully covered',
        'fully_received' => 'Fully received',
        'legacy_flow' => 'Completed under old dispatch flow',
        'cancelled' => 'Cancelled',
        'voided' => 'Voided',
    ];

    protected $fillable = [
        'portal_po_id', 'po_number', 'portal_project_id', 'project_name', 'project_id', 'supplier_name',
        'required_date', 'items', 'status', 'event_seq', 'legacy_flow', 'store_notes', 'updated_by',
        'voided_at', 'void_reason', 'voided_by_name',
    ];

    protected $casts = [
        'items' => 'array',
        'required_date' => 'date',
        'legacy_flow' => 'boolean',
        'voided_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function () {
            throw new LogicException('Purchase orders cannot be deleted.');
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function lines()
    {
        return $this->hasMany(PortalOrderLine::class)->orderBy('id');
    }

    public function entries()
    {
        return $this->hasMany(PoReceiptEntry::class)->orderBy('entry_date')->orderBy('id');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null || $this->status === 'voided';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Per-line breakdown from the NON-voided entries. Uses the loaded `lines` and `entries`
     * relations (loads them if missing). Percentages are never stored.
     */
    public function breakdown(): array
    {
        $this->loadMissing(['lines', 'entries']);

        $active = $this->entries->filter(function ($e) {
            return $e->voided_at === null;
        })->groupBy('portal_order_line_id');

        $items = [];
        foreach ($this->lines as $line) {
            $rows = $active->get($line->id, collect());
            $sum = function ($type) use ($rows) {
                return round((float) $rows->where('type', $type)->sum('quantity'), 3);
            };
            $atStore = $sum('store_receipt');
            $direct = $sum('direct_to_site');
            $stock = $sum('from_stock');
            $ordered = round((float) $line->quantity_ordered, 3);
            // Vendor received = what the vendor actually delivered (store + direct to site).
            $vendor = round($atStore + $direct, 3);
            $vendorDue = round(max($ordered - $vendor, 0), 3);
            // PO coverage also counts stock we issued from the store.
            $coverage = round(min($ordered, $vendor + $stock), 3);
            $toCover = round($ordered - $coverage, 3);

            if ($ordered > 0) {
                $percent = round($coverage / $ordered * 100, 2);
            } else {
                $percent = ($vendor + $stock) > 0 ? 100.0 : 0.0;
            }

            $items[] = [
                'line_id' => $line->id,
                'material_name' => $line->material_name,
                'item_code' => $line->item_code,
                'unit' => $line->unit,
                'ordered' => $ordered,
                'at_store' => $atStore,
                'direct_to_site' => $direct,
                'from_stock' => $stock,
                'vendor_received' => $vendor,
                'vendor_due' => $vendorDue,
                'coverage' => $coverage,
                'to_cover' => $toCover,
                'remaining' => $toCover,
                'over_delivered' => round(max($vendor - $ordered, 0), 3),
                'percent' => $percent,
            ];
        }
        return $items;
    }

    public function receivedPercent(?array $items = null): float
    {
        $items = $items ?? $this->breakdown();
        if (!$items) {
            return 0.0;
        }

        return round(array_sum(array_column($items, 'percent')) / count($items), 2);
    }

    public function statusKey(?array $items = null): string
    {
        if ($this->isVoided()) {
            return 'voided';
        }
        if ($this->isCancelled()) {
            return 'cancelled';
        }
        if ($this->legacy_flow) {
            return 'legacy_flow';
        }
        $items = $items ?? $this->breakdown();
        $touched = array_filter($items, function ($i) {
            return $i['at_store'] + $i['direct_to_site'] + $i['from_stock'] > 0;
        });
        if (!$touched) {
            return 'not_received';
        }
        foreach ($items as $i) {
            if ($i['percent'] < 100) {
                return 'partially_received';
            }
        }
        // Everything is covered; the vendor balance stays open while any vendor_due > 0.
        foreach ($items as $i) {
            if ($i['vendor_due'] > 0) {
                return 'fully_covered';
            }
        }

        return 'fully_received';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->statusKey()];
    }

    /**
     * Current store balance for each item, matched by name. incoming_material_items.quantity
     * is a running balance (outgoing/transfers subtract, returns add back).
     */
    public static function stockFor(array $items)
    {
        $names = collect($items)->map(function ($i) {
            return mb_strtolower(trim($i['material_name']));
        })->unique()->values();

        $stock = DB::table('incoming_material_items')
            ->selectRaw('LOWER(TRIM(item_name)) as name, unit, SUM(quantity) as qty')
            ->whereNull('deleted_at')
            ->whereIn(DB::raw('LOWER(TRIM(item_name))'), $names)
            ->groupBy(DB::raw('LOWER(TRIM(item_name))'), 'unit')
            ->get()
            ->groupBy('name');

        return collect($items)->map(function ($item) use ($stock) {
            $rows = $stock->get(mb_strtolower(trim($item['material_name'])), collect());
            $available = (float) $rows->sum('qty');
            $requested = (float) ($item['quantity'] ?? 0);

            return [
                'material_name' => $item['material_name'],
                'requested' => $requested,
                'available' => $available,
                'units' => $rows->pluck('unit')->unique()->values(),
                'status' => $available <= 0 ? 'not_in_stock' : ($available >= $requested ? 'in_stock' : 'partial'),
            ];
        })->values();
    }

    /**
     * The one serializer for the order: used for the API response AND the outbound event body.
     * Contains no prices.
     */
    public function toPortalArray(): array
    {
        $this->load(['lines', 'entries.line', 'entries.recorder', 'entries.sourceStock']);
        $items = $this->breakdown();

        $receipts = $this->entries->map(function ($e) {
            return [
                'id' => $e->id,
                'entry_date' => $e->entry_date->toDateString(),
                'type' => $e->type,
                'material_name' => optional($e->line)->material_name,
                'quantity' => (float) $e->quantity,
                'unit' => optional($e->line)->unit,
                'vendor_dn_number' => $e->vendor_dn_number,
                'site_receiver_name' => $e->site_receiver_name,
                'from_stock_label' => $e->stockLabel(),
                'note' => $e->note,
                'stock_issue' => $e->stock_issue_details,
                'recorded_by_name' => optional($e->recorder)->name,
                'voided' => $e->voided_at !== null,
                'void_reason' => $e->void_reason,
            ];
        })->values()->all();

        return [
            'portal_po_id' => $this->portal_po_id,
            'store_order_id' => $this->id,
            'status' => $this->statusKey($items),
            'received_percent' => $this->receivedPercent($items),
            'event_seq' => (int) $this->event_seq,
            'items' => array_map(function ($i) {
                unset($i['line_id']);

                return $i;
            }, $items),
            'receipts' => $receipts,
            'voided' => $this->isVoided(),
            'void_reason' => $this->void_reason,
            'voided_by_name' => $this->voided_by_name,
            'voided_at' => optional($this->voided_at)->toIso8601String(),
            'expected_stock_date' => null,
            'expected_delivery_date' => null,
        ];
    }
}
