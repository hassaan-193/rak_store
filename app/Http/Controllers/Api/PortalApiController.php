<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortalOrder;
use App\Models\Project;
use App\Services\Integration\PoReceiptService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Endpoints the FTS portal calls. All routes are behind VerifyPortalSignature.
 */
class PortalApiController extends Controller
{
    public function ping()
    {
        return response()->json(['ok' => true, 'app' => 'store', 'time' => now()->toIso8601String()]);
    }

    /** Stock check: how much of each requested item the store currently holds. */
    public function stockCheck(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|max:200',
            'items.*.material_name' => 'required|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        return response()->json(['items' => PortalOrder::stockFor($data['items']), 'checked_at' => now()->toIso8601String()]);
    }

    /** The portal sends an approved PO; created once, later calls update the details and items. */
    public function upsertPurchaseOrder(Request $request, PoReceiptService $service)
    {
        $data = $request->validate([
            'portal_po_id' => 'required|integer|min:1',
            'po_number' => 'nullable|string|max:100',
            'project.id' => 'nullable|integer',
            'project.name' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'required_date' => 'nullable|date',
            'items' => 'required|array|max:200',
            'items.*.material_name' => 'required|string|max:255',
            'items.*.item_code' => 'nullable|string|max:100',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'cancelled' => 'nullable|boolean',
        ]);

        $projectName = $data['project']['name'] ?? null;
        $storeProject = $projectName ? Project::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($projectName))])->first() : null;

        // Prices are never accepted: only these four fields of each item are kept.
        $items = array_map(function ($i) {
            return array_intersect_key($i, array_flip(['material_name', 'item_code', 'unit', 'quantity']));
        }, $data['items']);

        $outboxId = null;
        $created = false;
        $ignoredVoided = false;

        $order = DB::transaction(function () use ($data, $projectName, $storeProject, $items, $service, &$created, &$outboxId, &$ignoredVoided) {
            $order = PortalOrder::lockForUpdate()->where('portal_po_id', $data['portal_po_id'])->first();
            if ($order && $order->isVoided()) {
                // A stale resend must never reopen or edit an archived PO.
                $ignoredVoided = true;

                return $order;
            }
            if (!$order) {
                try {
                    $order = PortalOrder::create(['portal_po_id' => $data['portal_po_id'], 'status' => 'received']);
                } catch (QueryException $e) {
                    // two first sends at once: the other one won, update that row
                    $order = PortalOrder::lockForUpdate()->where('portal_po_id', $data['portal_po_id'])->firstOrFail();
                }
                $created = $order->wasRecentlyCreated;
            }

            $order->fill([
                'po_number' => $data['po_number'] ?? null,
                'portal_project_id' => $data['project']['id'] ?? null,
                'project_name' => $projectName,
                'project_id' => optional($storeProject)->id,
                'supplier_name' => $data['supplier_name'] ?? null,
                'required_date' => $data['required_date'] ?? null,
                'items' => $items,
                'status' => !empty($data['cancelled']) ? 'cancelled' : 'received',
            ]);
            $changed = $order->isDirty() || $created;
            $order->save();

            if ($service->syncLines($order, $items) || $changed) {
                $outboxId = $service->bumpAndQueue($order);
            }

            return $order;
        });

        $service->deliverQuietly($outboxId);

        if ($ignoredVoided) {
            // 200 so the portal outbox stops retrying; nothing was changed.
            return response()->json(['message' => 'ignored_voided', 'order' => $order->toPortalArray()]);
        }

        return response()->json(['message' => $created ? 'created' : 'updated', 'order' => $order->toPortalArray()], $created ? 201 : 200);
    }

    /** Backup check: the portal can ask for the latest state at any time. */
    public function showPurchaseOrder($portalPoId)
    {
        $order = PortalOrder::where('portal_po_id', $portalPoId)->first();

        if (!$order) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['order' => $order->toPortalArray()]);
    }

    /** Confirmed whole-PO void from the portal: archive the order, keep all movements and history. */
    public function voidPurchaseOrder(Request $request, $portalPoId, PoReceiptService $service)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
            'voided_by' => 'nullable|string|max:255',
            'voided_at' => 'nullable|date',
        ]);

        $order = $service->voidOrder((int) $portalPoId, $data['reason'], $data['voided_by'] ?? null, $data['voided_at'] ?? null);

        return response()->json(['message' => 'voided', 'order' => $order->toPortalArray()]);
    }
}
