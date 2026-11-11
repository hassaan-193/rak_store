<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\IncomingMaterial;
use App\Models\IncomingMaterialItem;
use App\Models\OutgoingMaterial;
use App\Models\PortalOrder;
use App\Models\Project;
use App\Models\ReturnMaterial;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolReturn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Recycle Bin: list recycled records and restore them.
 * There is deliberately NO delete / empty-bin / force-delete action here or anywhere.
 */
class RecycleBinController extends Controller
{
    /** route key => [model class, label] */
    public static $types = [
        'incoming-materials' => [IncomingMaterial::class, 'Incoming receipt'],
        'outgoing-materials' => [OutgoingMaterial::class, 'Outgoing request'],
        'return-materials' => [ReturnMaterial::class, 'Return material'],
        'projects' => [Project::class, 'Project'],
        'suppliers' => [Supplier::class, 'Supplier'],
        'drivers' => [Driver::class, 'Driver'],
        'tools' => [Tool::class, 'Tool'],
        'tool-assignments' => [ToolAssignment::class, 'Tool assignment'],
        'tool-returns' => [ToolReturn::class, 'Tool return'],
        'users' => [User::class, 'User'],
    ];

    public function index(Request $request)
    {
        $filter = $request->query('type');
        $keys = ($filter && isset(self::$types[$filter])) ? [$filter] : array_keys(self::$types);

        $rows = collect();
        foreach ($keys as $key) {
            $class = self::$types[$key][0];
            $label = self::$types[$key][1];
            foreach ($class::onlyTrashed()->get() as $record) {
                $rows->push((object) [
                    'type' => $key,
                    'type_label' => $label,
                    'id' => $record->id,
                    'label' => $this->describe($key, $record),
                    'deleted_by' => $record->deleted_by,
                    'deleted_at' => $record->deleted_at,
                    'reason' => $record->deletion_reason,
                ]);
            }
        }

        $rows = $rows->sortByDesc('deleted_at')->values();
        $names = User::withTrashed()->whereIn('id', $rows->pluck('deleted_by')->filter()->unique())->pluck('name', 'id');

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $entries = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('recycle-bin.index', [
            'entries' => $entries,
            'names' => $names,
            'types' => self::$types,
            'filter' => $filter,
            'voidedOrders' => PortalOrder::whereNotNull('voided_at')->orderByDesc('voided_at')->get(),
        ]);
    }

    public function restore($type, $id)
    {
        if (!isset(self::$types[$type])) {
            abort(404);
        }
        $class = self::$types[$type][0];
        $label = self::$types[$type][1];
        $record = $class::onlyTrashed()->findOrFail($id);

        try {
            DB::transaction(function () use ($type, $record) {
                $this->guardRestore($type, $record);
                $record->restore();
                $this->applyStockOnRestore($type, $record);
            });
        } catch (\DomainException $e) {
            return redirect()->route('recycle-bin.index', request()->query())->with('error', $e->getMessage());
        }

        return redirect()->route('recycle-bin.index', request()->query())
            ->with('success', $label . ' restored successfully.');
    }

    private function describe($type, $r)
    {
        switch ($type) {
            case 'incoming-materials':
                return '#' . $r->id . ' - DN ' . $r->dn_number . ' (' . $this->childrenOf($r)->count() . ' items)';
            case 'outgoing-materials':
                return '#' . $r->id . ' - DN ' . $r->dn_number . ' (' . $r->status . ')';
            case 'return-materials':
                return '#' . $r->id . ($r->return_reason ? ' - ' . Str::limit($r->return_reason, 50) : '');
            case 'tools':
                return $r->item_name . ' (' . $r->item_code . ')';
            case 'tool-assignments':
                return '#' . $r->id . ' - ' . $r->item_name . ' to ' . $r->worker_name . ' (qty ' . $r->quantity_issued . ')';
            case 'tool-returns':
                return '#' . $r->id . ' - qty ' . $r->quantity_returned . ' for assignment #' . $r->tool_assignment_id;
            case 'users':
                return $r->name . ' (' . $r->email . ')';
            default:
                return $r->name;
        }
    }

    /** Items that were recycled together with this parent (same deleted_at stamp). */
    private function childrenOf($parent)
    {
        $stamp = $parent->getRawOriginal('deleted_at');
        if ($parent instanceof IncomingMaterial) {
            $relation = $parent->items();
        } elseif ($parent instanceof OutgoingMaterial) {
            $relation = $parent->items();
        } else {
            $relation = $parent->materials();
        }
        return $relation->onlyTrashed()->where($relation->getRelated()->getQualifiedDeletedAtColumn(), $stamp)
            ->where($relation->getRelated()->qualifyColumn('deletion_reason'), IncomingMaterial::CASCADE_MARK)->get();
    }

    /** Refuse restores that would clash with an active record or cannot be re-applied. */
    private function guardRestore($type, $r)
    {
        $dup = false;
        $msg = '';
        if (in_array($type, ['projects', 'suppliers', 'drivers'])) {
            $class = self::$types[$type][0];
            $dup = $class::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($r->name))])->exists();
            $msg = 'An active ' . strtolower(self::$types[$type][1]) . ' named "' . $r->name . '" already exists. Rename it first.';
        } elseif ($type === 'tools') {
            $dup = Tool::where('item_code', $r->item_code)->exists();
            $msg = 'An active tool with item code "' . $r->item_code . '" already exists.';
        } elseif ($type === 'users') {
            $dup = User::where('email', $r->email)->exists();
            $msg = 'An active user with email "' . $r->email . '" already exists.';
        }
        if ($dup) {
            throw new \DomainException($msg);
        }

        if ($type === 'tool-returns') {
            $assignment = ToolAssignment::find($r->tool_assignment_id);
            if (!$assignment) {
                throw new \DomainException('The tool assignment of this return is in the Recycle Bin. Restore the assignment first.');
            }
            if ($assignment->quantity_issued < $r->quantity_returned) {
                throw new \DomainException('Cannot restore: the assignment now has only ' . $assignment->quantity_issued . ' issued, less than the ' . $r->quantity_returned . ' being returned.');
            }
        }

        if ($type === 'tool-assignments') {
            $tool = Tool::where('item_code', $r->item_code)->first();
            if ($tool && $tool->quantity < $r->quantity_issued) {
                throw new \DomainException('Cannot restore: only ' . $tool->quantity . ' of "' . $tool->item_name . '" is in the store, ' . $r->quantity_issued . ' needed.');
            }
        }

        if ($type === 'outgoing-materials' && $r->status === 'approved') {
            $problems = [];
            foreach ($this->childrenOf($r) as $item) {
                $stock = $this->stockFor($r, $item);
                if ($stock && $stock->quantity < $item->quantity) {
                    $problems[] = "'{$item->item_name}' (available {$stock->quantity}, needed {$item->quantity})";
                }
            }
            if ($problems) {
                throw new \DomainException('Cannot restore: stock is no longer available for ' . implode(', ', $problems) . '.');
            }
        }
    }

    /** Re-apply the stock effect that was reversed when the record was deleted. */
    private function applyStockOnRestore($type, $r)
    {
        if ($type === 'outgoing-materials' && $r->status === 'approved') {
            $allocator = app(\App\Services\OutgoingStockAllocator::class);
            foreach ($r->items()->get() as $item) {
                // re-deduct across the eligible rows and record the new allocation
                $allocator->allocate($r, $item, (float) $item->quantity);
            }
        } elseif ($type === 'tool-assignments') {
            $tool = Tool::where('item_code', $r->item_code)->lockForUpdate()->first();
            if ($tool) {
                $tool->decrement('quantity', $r->quantity_issued);
            }
        } elseif ($type === 'tool-returns') {
            $assignment = ToolAssignment::find($r->tool_assignment_id);
            if ($assignment) {
                if ($assignment->tool) {
                    $assignment->tool->increment('quantity', $r->quantity_returned);
                }
                $assignment->decrement('quantity_issued', $r->quantity_returned);
            }
        }
    }

    private function stockFor($outgoing, $item)
    {
        return IncomingMaterialItem::where('item_name', $item->item_name)
            ->where('category', $item->category)
            ->whereHas('incomingMaterial', function ($q) use ($outgoing) {
                $q->where('project_id', $outgoing->project_id)
                  ->where('project_type', $outgoing->project_type);
            })
            ->lockForUpdate()
            ->first();
    }
}
