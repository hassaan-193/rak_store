<?php

namespace App\Services;

use App\Models\IncomingMaterialItem;
use App\Models\OutgoingMaterial;
use App\Models\OutgoingMaterialItem;
use DomainException;

/**
 * Deducts an approved Outgoing quantity across every matching stock row (oldest first) and
 * records the exact rows and quantities, so a reversal puts back precisely what was taken, once.
 * Call inside a DB transaction. Matching is the same as before: item name + category + project scope.
 */
class OutgoingStockAllocator
{
    const EPS = 0.0005;

    protected function rows(OutgoingMaterial $outgoing, OutgoingMaterialItem $item)
    {
        return IncomingMaterialItem::where('item_name', $item->item_name)
            ->where('category', $item->category)
            ->whereHas('incomingMaterial', function ($q) use ($outgoing) {
                $q->where('project_id', $outgoing->project_id)
                  ->where('project_type', $outgoing->project_type);
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /** Total stock currently available for the item (for validation before approving). */
    public function available(OutgoingMaterial $outgoing, OutgoingMaterialItem $item): ?float
    {
        $rows = IncomingMaterialItem::where('item_name', $item->item_name)
            ->where('category', $item->category)
            ->whereHas('incomingMaterial', function ($q) use ($outgoing) {
                $q->where('project_id', $outgoing->project_id)
                  ->where('project_type', $outgoing->project_type);
            })->get();

        return $rows->isEmpty() ? null : (float) $rows->sum('quantity');
    }

    /**
     * Deduct $qty across the rows and store the allocation on the item.
     * No matching stock row at all: nothing is deducted (the previous behaviour).
     *
     * @throws DomainException when matching rows exist but hold less than $qty
     */
    public function allocate(OutgoingMaterial $outgoing, OutgoingMaterialItem $item, float $qty): array
    {
        $rows = $this->rows($outgoing, $item);
        $allocations = [];

        if ($rows->isNotEmpty() && $qty > 0) {
            $total = (float) $rows->sum('quantity');
            if ($total + self::EPS < $qty) {
                throw new DomainException("Insufficient stock for '{$item->item_name}'. Available: {$total}, requested: {$qty}.");
            }
            $left = $qty;
            foreach ($rows as $row) {
                if ($left <= self::EPS) {
                    break;
                }
                $have = (float) $row->quantity;
                if ($have <= 0) {
                    continue;
                }
                $take = round(min($have, $left), 2);
                if ($take <= 0) {
                    continue;
                }
                $row->quantity = round($have - $take, 2);
                $row->save();
                $allocations[] = ['stock_item_id' => $row->id, 'quantity' => $take, 'balance_before' => $have, 'balance_after' => (float) $row->quantity];
                $left = round($left - $take, 2);
            }
        }

        $item->stock_allocations = $allocations;
        $item->allocations_reversed_at = null;
        $item->save();

        return $allocations;
    }

    /**
     * Put back what was deducted: to the recorded rows, exactly once. Approvals made before
     * allocations were recorded fall back to the first matching row, as before.
     */
    public function reverse(OutgoingMaterial $outgoing, OutgoingMaterialItem $item): void
    {
        if ($item->allocations_reversed_at) {
            return;
        }

        $allocations = $item->stock_allocations;
        if (is_array($allocations)) {
            foreach ($allocations as $a) {
                $row = IncomingMaterialItem::withTrashed()->lockForUpdate()->find($a['stock_item_id']);
                if ($row) {
                    $row->quantity = round((float) $row->quantity + (float) $a['quantity'], 2);
                    $row->save();
                }
            }
        } else {
            $row = $this->rows($outgoing, $item)->first();
            if ($row) {
                $row->quantity = round((float) $row->quantity + (float) $item->quantity, 2);
                $row->save();
            }
        }

        $item->allocations_reversed_at = now();
        $item->save();
    }
}
