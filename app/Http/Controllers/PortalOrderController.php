<?php

namespace App\Http\Controllers;

use App\Models\PoReceiptEntry;
use App\Models\PortalOrder;
use App\Services\Integration\PoReceiptService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Store-side screen for purchase orders approved in the FTS portal: shows how much of each
 * PO was received at the store, sent direct to site, or supplied from store stock.
 * Entries are never deleted, only voided. Every change is pushed back to the portal.
 */
class PortalOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PortalOrder::with(['lines', 'entries'])
            ->whereNull('voided_at')
            ->when($request->project, function ($q, $project) {
                $q->where('project_name', $project);
            })
            ->when($request->supplier, function ($q, $supplier) {
                $q->where('supplier_name', $supplier);
            })
            ->latest()
            ->get();

        // Status is computed from the entries, so it is filtered here, not in SQL.
        $rows = $orders->map(function ($o) {
            $items = $o->breakdown();

            return (object) ['order' => $o, 'status' => $o->statusKey($items), 'percent' => $o->receivedPercent($items)];
        });
        if ($request->status) {
            $rows = $rows->where('status', $request->status);
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator($rows->forPage($page, 25)->values(), $rows->count(), 25, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $projects = PortalOrder::whereNotNull('project_name')->distinct()->orderBy('project_name')->pluck('project_name');
        $suppliers = PortalOrder::whereNotNull('supplier_name')->distinct()->orderBy('supplier_name')->pluck('supplier_name');

        $outbox = DB::table('integration_messages')->where('direction', 'out')
            ->selectRaw("SUM(status = 'pending') as pending, SUM(status = 'failed') as failed")->first();

        return view('portal-orders.index', compact('rows', 'outbox', 'projects', 'suppliers'));
    }

    public function show(PortalOrder $portalOrder, PoReceiptService $service)
    {
        $portalOrder->load(['lines', 'entries.line', 'entries.recorder', 'entries.sourceStock']);
        $items = $portalOrder->breakdown();

        // Eligible stock per line (same item + unit, General or this PO's project, never this PO's own receipts).
        $stockByLine = [];
        if (!$portalOrder->isVoided() && !$portalOrder->isCancelled() && !$portalOrder->legacy_flow) {
            foreach ($portalOrder->lines as $line) {
                $stockByLine[$line->id] = $service->eligibleStock($portalOrder, $line);
            }
        }

        return view('portal-orders.show', [
            'portalOrder' => $portalOrder,
            'items' => $items,
            'statusKey' => $portalOrder->statusKey($items),
            'percent' => $portalOrder->receivedPercent($items),
            'stockByLine' => $stockByLine,
            'canEnter' => !$portalOrder->isVoided() && !$portalOrder->isCancelled() && !$portalOrder->legacy_flow,
        ]);
    }

    /** Record a delivery entry: store receipt, direct to site, or supplied from stock. */
    public function storeEntry(Request $request, PortalOrder $portalOrder, PoReceiptService $service)
    {
        $type = $request->input('type');

        $rules = [
            'type' => 'required|in:' . implode(',', PoReceiptEntry::TYPES),
            'portal_order_line_id' => 'required|integer',
            'quantity' => 'required|numeric|gt:0|max:99999999',
            'entry_date' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:1000',
            'submission_uuid' => 'nullable|string|max:64',
        ];
        if (in_array($type, ['store_receipt', 'direct_to_site'], true)) {
            $rules['vendor_dn_number'] = 'required|string|max:100';
        }
        if ($type === 'store_receipt') {
            $rules['category'] = 'nullable|string|max:255';
            $rules['store_location'] = 'required|string|max:255';
            $rules['stock_scope'] = 'nullable|in:general,project';
        }
        if ($type === 'direct_to_site') {
            $rules['site_receiver_name'] = 'required|string|max:255';
            $rules['dn_file'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }
        if ($type === 'from_stock') {
            $rules['source_stock_item_id'] = 'required|integer|exists:incoming_material_items,id';
            $rules['issue_destination'] = 'required|string|max:255';
        }

        $data = $request->validate($rules);

        // Stock rows keep 2 decimals.
        if ($type !== 'direct_to_site' && abs(round($data['quantity'], 2) - $data['quantity']) > 0.0000001) {
            return back()->withInput()->withErrors(['quantity' => 'Quantity can have at most 2 decimals for entries that move stock.']);
        }

        try {
            $service->recordEntry($portalOrder, $data, $request->file('dn_file'), auth()->id());
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['entry' => $e->getMessage()]);
        }

        return redirect()->route('portal-orders.show', $portalOrder)->with('success', $service->lastReplayed ? 'This submission was already recorded; nothing was changed.' : 'Entry recorded and sent to the FTS portal.');
    }

    public function voidEntry(Request $request, PortalOrder $portalOrder, PoReceiptEntry $entry, PoReceiptService $service)
    {
        abort_unless($entry->portal_order_id === $portalOrder->id, 404);
        $data = $request->validate(['void_reason' => 'required|string|max:1000']);

        try {
            $service->voidEntry($entry, $data['void_reason'], auth()->id());
        } catch (DomainException $e) {
            return back()->withErrors(['void' => $e->getMessage()]);
        }

        return redirect()->route('portal-orders.show', $portalOrder)->with('success', 'Entry voided and the portal updated.');
    }

    /** Signed DN photo/PDF of a direct-to-site entry; only through this authenticated route. */
    public function downloadDn(PortalOrder $portalOrder, PoReceiptEntry $entry)
    {
        abort_unless($entry->portal_order_id === $portalOrder->id, 404);
        abort_unless($entry->dn_file_path && Storage::exists($entry->dn_file_path), 404);

        return Storage::download($entry->dn_file_path, 'DN-' . ($entry->vendor_dn_number ?: $entry->id) . '.' . pathinfo($entry->dn_file_path, PATHINFO_EXTENSION));
    }

    /** Manually resend the current state (e.g. after the portal was down). */
    public function resync(PortalOrder $portalOrder, PoReceiptService $service)
    {
        $id = null;
        abort_if($portalOrder->isVoided(), 403, 'Voided purchase orders are archived.');
        DB::transaction(function () use ($portalOrder, $service, &$id) {
            $locked = PortalOrder::lockForUpdate()->findOrFail($portalOrder->id);
            $id = $service->bumpAndQueue($locked);
        });
        $service->deliverQuietly($id);

        return back()->with('success', 'Current status re-sent to the FTS portal.');
    }
}
