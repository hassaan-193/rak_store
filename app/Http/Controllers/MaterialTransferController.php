<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Supplier;
use App\Models\Driver;
use App\Models\Project;
use App\Models\IncomingMaterialItem;
use Illuminate\Http\Request;

class MaterialTransferController extends Controller
{    
    public function __construct()
    {
        $this->middleware('permission:show')->only(['index', 'show']);
        $this->middleware('permission:create')->only(['create', 'store']);
        $this->middleware('permission:edit')->only(['edit', 'update']);
        $this->middleware('permission:delete')->only(['destroy']);
    }
    // Display available items for transfer
    public function index()
    {
        $projectItems = IncomingMaterialItem::whereHas('incomingMaterial', function ($query) {
            $query->where('project_type', 'project');
        })->get();

        return view('materials.transfer.index', compact('projectItems'));
    }

    // Show the transfer form
    public function create(IncomingMaterialItem $item)
    {
        return view('materials.transfer.create', compact('item'));
    }

    // Handle the transfer action
    public function store(Request $request, IncomingMaterialItem $item)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1|max:' . $item->quantity,
        ]);

        $transferQty = $request->quantity;

        // Step 1: Reduce quantity or delete if none left
        $item->quantity -= $transferQty;

        if ($item->quantity <= 0) {
            $item->delete();
        } else {
            $item->save();
        }

        // Step 2: Create/find general material entry
        $generalMaterial = IncomingMaterial::firstOrCreate([
            'project_type' => 'general',
            'store_location' => $item->incomingMaterial->store_location,
            'dn_number' => $item->incomingMaterial->dn_number,
        ], [
            'date_time' => now(),
            'source_type' => $item->incomingMaterial->source_type,
            'driver_id' => $item->incomingMaterial->driver_id,
            'supplier_id' => $item->incomingMaterial->supplier_id,
            'engineer_name' => $item->incomingMaterial->engineer_name,
        ]);

        // Keep the PO link so transferred stock stays traceable to the PO that received it.
        if ($generalMaterial->wasRecentlyCreated && $item->incomingMaterial->portal_order_id) {
            $generalMaterial->portal_order_id = $item->incomingMaterial->portal_order_id;
            $generalMaterial->save();
        }

        // Step 3: Create new item under general
        $generalMaterial->items()->create([
            'item_name' => $item->item_name,
            'category' => $item->category,
            'brand' => $item->brand,
            'unit' => $item->unit,
            'quantity' => $transferQty,
        ]);

        // Step 4: Delete the parent material if no items left
        if ($item->incomingMaterial->items()->count() === 0) {
            $item->incomingMaterial->delete();
        }

        return redirect()->route('materials.transfer.index')
            ->with('success', 'Item successfully transferred to General.');
    }
}
