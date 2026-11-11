<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Models\OutgoingMaterial;
use App\Models\IncomingMaterial;

use App\Models\OutgoingMaterialItem;
use App\Models\IncomingMaterialItem;
use App\Models\Driver;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Import Carbon here

class OutgoingMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show')->only(['index', 'show']);
        $this->middleware('permission:create')->only(['create', 'store']);
        $this->middleware('permission:edit')->only(['edit', 'update']);
        $this->middleware('permission:delete')->only(['destroy']);
    }
    public function index()
    {
        $outgoingMaterials = OutgoingMaterial::with('driver', 'project', 'creator')
            ->where('status', 'pending')
            ->get();
        return view('outgoing-materials.index', compact('outgoingMaterials'));
    }

    public function pending()
    {
        $outgoingMaterials = OutgoingMaterial::with('driver', 'project', 'creator')
            ->where('status', 'pending')
            ->get();
        return view('outgoing-materials.pending', compact('outgoingMaterials'));
    }

    public function processed()
    {
        $outgoingMaterials = OutgoingMaterial::with('driver', 'project', 'approver', 'rejecter')
            ->whereIn('status', ['approved', 'rejected'])
            ->get();
        return view('outgoing-materials.processed', compact('outgoingMaterials'));
    }

    public function create()
    {
        $drivers = Driver::pluck('name', 'id');
        $projects = Project::all();
        return view('outgoing-materials.create', compact('drivers', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_time' => 'required|date',
            'driver_id' => 'required|exists:drivers,id',
            'project_type' => 'required|in:general,project',
            'project_id' => 'nullable|exists:projects,id',
            'engineer_name' => 'required|string',
            'dn_number' => 'required|string',
            'site' => $request->project_type === 'general' ? 'required|string' : 'nullable|string', 
            'materials' => 'required|array',
            'materials.*.item_name' => 'required|string',
            'materials.*.category' => 'required|string',
            'materials.*.unit' => 'required|string',
            'materials.*.quantity' => 'nullable|numeric|min:0',
        ]);
    
        // Create the request with status 'pending'
        $outgoingMaterial = OutgoingMaterial::create([
            'date_time' => Carbon::parse($request->date_time),
            'driver_id' => $request->driver_id,
            'project_type' => $request->project_type,
            'project_id' => $request->project_type == 'project' ? $request->project_id : null,
            'engineer_name' => $request->engineer_name,
            'dn_number' => $request->dn_number,
            'site' => $request->site,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);
    
        // Save item lines only, no quantity deduction yet
        $materials = collect($request->materials)->filter(function ($material) {
            return isset($material['quantity']) && $material['quantity'] > 0;
        });
    
        foreach ($materials as $material) {
            $outgoingMaterialItem = new OutgoingMaterialItem([
                'item_name' => $material['item_name'],
                'category' => $material['category'],
                'unit' => $material['unit'],
                'quantity' => $material['quantity'],
            ]);
    
            $outgoingMaterial->items()->save($outgoingMaterialItem);
        }
    
        return redirect()->route('outgoing-materials.index')->with('success', 'Outgoing Material request submitted successfully and pending approval.');
    }
    
    
    
    

    public function show(OutgoingMaterial $outgoingMaterial)
    {
        $outgoingMaterial->load('driver', 'project', 'items');
        return view('outgoing-materials.show', compact('outgoingMaterial'));
    }

    public function edit($id)
    {
        $outgoingMaterial = OutgoingMaterial::with('items')->findOrFail($id);
    
        if ($outgoingMaterial->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be edited.');
        }
    
        $outgoingMaterial->date_time = Carbon::parse($outgoingMaterial->date_time);
        $drivers = Driver::pluck('name', 'id');
        $projects = Project::pluck('name', 'id');
    
        return view('outgoing-materials.edit', compact('outgoingMaterial', 'drivers', 'projects'));
    }
    
    
    
    

    public function update(Request $request, $id)
    {
        $request->validate([
            'date_time' => 'required|date',
            'driver_id' => 'required|exists:drivers,id',
            'project_type' => 'required|in:general,project',
            'project_id' => 'nullable|exists:projects,id',
            'engineer_name' => 'required|string',
            'dn_number' => 'required|string',
            'site' => $request->project_type === 'general' ? 'required|string' : 'nullable|string',
            'materials' => 'required|array',
            'materials.*.item_name' => 'required|string',
            'materials.*.category' => 'required|string',
            'materials.*.unit' => 'required|string',
            'materials.*.quantity' => 'nullable|numeric|min:0',
        ]);
    
        $outgoingMaterial = OutgoingMaterial::findOrFail($id);
    
        if ($outgoingMaterial->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be edited.');
        }
    
        $outgoingMaterial->items()->delete(); // Just delete, no inventory rollback
    
        $outgoingMaterial->update([
            'date_time' => Carbon::parse($request->date_time),
            'driver_id' => $request->driver_id,
            'project_type' => $request->project_type,
            'project_id' => $request->project_type == 'project' ? $request->project_id : null,
            'engineer_name' => $request->engineer_name,
            'dn_number' => $request->dn_number,
            'site' => $request->site,
        ]);
    
        $materials = collect($request->materials)->filter(fn ($m) => isset($m['quantity']) && $m['quantity'] > 0);
    
        foreach ($materials as $material) {
            $outgoingMaterial->items()->create([
                'item_name' => $material['item_name'],
                'category' => $material['category'],
                'unit' => $material['unit'],
                'quantity' => $material['quantity'],
            ]);
        }
    
        return redirect()->route('outgoing-materials.index')->with('success', 'Outgoing request updated successfully.');
    }
    
    
    
    
public function destroy($id)
{
    $outgoingMaterial = OutgoingMaterial::with('items')->findOrFail($id);

    // Only allow deletion of pending and approved requests
    if (!in_array($outgoingMaterial->status, ['pending', 'approved'])) {
        return redirect()->back()->with('error', 'Only pending or approved requests can be deleted.');
    }

    DB::transaction(function () use ($outgoingMaterial) {
        // Stock is only deducted when a request is approved, so only an approved
        // request has anything to put back. A pending one never touched stock.
        if ($outgoingMaterial->status === 'approved') {
            $allocator = app(\App\Services\OutgoingStockAllocator::class);
            foreach ($outgoingMaterial->items as $item) {
                $allocator->reverse($outgoingMaterial, $item);
            }
        }

        // Items are recycled together with the request
        $outgoingMaterial->delete();
    });

    return redirect()->back()->with('success', 'Outgoing Material deleted successfully!');
}

    
    



    public function getMaterials(Request $request)
{
    $projectType = $request->project_type;
    $projectId = $request->project_id;

    $query = IncomingMaterialItem::query()
        ->select('item_name', 'category', 'unit', \DB::raw('SUM(quantity) as quantity'))
        ->join('incoming_materials', 'incoming_material_items.incoming_material_id', '=', 'incoming_materials.id')
        ->whereNull('incoming_materials.deleted_at')
        ->groupBy('item_name', 'category', 'unit');

    if ($projectType == 'project' && $projectId) {
        $query->where('incoming_materials.project_id', $projectId);
    } elseif ($projectType == 'general') {
        $query->whereNull('incoming_materials.project_id');
    }

    $materials = $query->get();

    return response()->json($materials);
}








public function showApprovalForm($id)
{
    $outgoingMaterial = OutgoingMaterial::with('items')->findOrFail($id);
    return view('outgoing-materials.approve', compact('outgoingMaterial'));
}

public function approve(Request $request, $id)
{
    $request->validate([
        'materials' => 'required|array',
        'materials.*.quantity' => 'required|numeric|min:0',
        'approval_comment' => 'nullable|string',
    ]);

    $outgoingMaterial = OutgoingMaterial::with('items')->findOrFail($id);

    if ($outgoingMaterial->status !== 'pending') {
        return back()->with('error', 'This request has already been processed.');
    }

    $validationErrors = [];

    foreach ($request->materials as $itemId => $approvedQty) {
        $item = $outgoingMaterial->items->where('id', $itemId)->first();
        if (!$item) continue;

        $approvedQuantity = is_array($approvedQty) ? $approvedQty['quantity'] : $approvedQty;

        if ($approvedQuantity > $item->quantity) {
            $validationErrors["materials.$itemId.quantity"] =
                "Approved quantity for '{$item->item_name}' cannot exceed requested quantity ({$item->quantity}).";
            continue;
        }

        $availableQty = app(\App\Services\OutgoingStockAllocator::class)->available($outgoingMaterial, $item);

        if ($availableQty !== null && $availableQty + 0.0005 < $approvedQuantity) {
            $validationErrors["materials.$itemId.quantity"] =
                "Insufficient stock for '{$item->item_name}'. Available: {$availableQty}, requested: $approvedQuantity.";
            continue;
        }
    }

    if (!empty($validationErrors)) {
        return back()->withErrors($validationErrors)->withInput();
    }

    // ✅ Everything validated, perform update
    try {
    DB::transaction(function () use ($request, $outgoingMaterial) {
        $allocator = app(\App\Services\OutgoingStockAllocator::class);
        // lock the request so two approvals cannot both deduct
        $locked = OutgoingMaterial::lockForUpdate()->findOrFail($outgoingMaterial->id);
        if ($locked->status !== 'pending') {
            throw new \DomainException('This request has already been processed.');
        }
        foreach ($request->materials as $itemId => $approvedQty) {
            $item = $outgoingMaterial->items->where('id', $itemId)->first();
            if (!$item) continue;

            $approvedQuantity = is_array($approvedQty) ? $approvedQty['quantity'] : $approvedQty;

            $item->quantity = $approvedQuantity;
            $item->save();

            // Deduct across every eligible row and record exactly which ones.
            $allocator->allocate($outgoingMaterial, $item, (float) $approvedQuantity);
        }

        $outgoingMaterial->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'approval_comment' => $request->approval_comment,
        ]);
    });
    } catch (\DomainException $e) {
        return back()->with('error', $e->getMessage())->withInput();
    }

    return redirect()->route('outgoing-materials.index')->with('success', 'Request approved successfully.');
}







public function reject(Request $request, $id)
{
    $request->validate([
        'approval_comment' => 'required|string|min:3',
    ]);

    $outgoingMaterial = OutgoingMaterial::findOrFail($id);

    if ($outgoingMaterial->status !== 'pending') {
        return back()->with('error', 'This request has already been processed.');
    }

    $outgoingMaterial->update([
        'status' => 'rejected',
        'rejected_at' => now(),
        'rejected_by' => Auth::id(),
        'approval_comment' => $request->approval_comment,
    ]);

    return redirect()->route('outgoing-materials.index')->with('success', 'Request rejected successfully.');
}

}
