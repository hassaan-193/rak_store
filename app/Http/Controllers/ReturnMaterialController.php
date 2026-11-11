<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\IncomingMaterialItem;
use App\Models\OutgoingMaterialItem;
use App\Models\Project;
use App\Models\ReturnMaterial;
use App\Models\ReturnMaterialItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReturnMaterialController extends Controller
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
        $returnMaterials = ReturnMaterial::latest()->get();
        return view('return-materials.index', compact('returnMaterials'));
    }

    public function create()
    {
        $drivers = Driver::all()->pluck('name', 'id');
        $projects = Project::all();
        return view('return-materials.create', compact('drivers', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_time' => 'required|date',
            'driver_id' => 'required|exists:drivers,id',
            'project_type' => 'required|in:general,project',
            'project_id' => 'nullable|exists:projects,id',
            'return_reason' => 'required|string|max:255',
            'materials' => 'required|array',
            'materials.*.quantity' => 'required|integer|min:0',
        ]);

        $returnMaterial = ReturnMaterial::create([
            'date_time' => Carbon::parse($request->date_time),
            'driver_id' => $request->driver_id,
            'project_type' => $request->project_type,
            'project_id' => $request->project_id,
            'return_reason' => $request->return_reason,
        ]);

        foreach ($request->materials as $materialData) {
            if ($materialData['quantity'] > 0) {
                $returnMaterial->materials()->create([
                    'item_name' => $materialData['item_name'],
                    'category' => $materialData['category'],
                    'unit' => $materialData['unit'],
                    'quantity' => $materialData['quantity'],
                ]);

                // Decrease from outgoing
                $outgoingItem = OutgoingMaterialItem::whereHas('outgoingMaterial', function ($query) use ($request) {
                    $query->where('project_id', $request->project_id);
                })
                ->where('item_name', $materialData['item_name'])
                ->where('category', $materialData['category'])
                ->where('unit', $materialData['unit'])
                ->first();

                if ($outgoingItem) {
                    $outgoingItem->quantity -= $materialData['quantity'];
                    $outgoingItem->save();
                }

                // Increase in incoming
                $incomingItem = IncomingMaterialItem::where('item_name', $materialData['item_name'])
                                    ->where('category', $materialData['category'])
                                    ->where('unit', $materialData['unit'])
                                    ->first();

                if ($incomingItem) {
                    $incomingItem->quantity += $materialData['quantity'];
                    $incomingItem->save();
                }
            }
        }

        return redirect()->route('return-materials.index')->with('success', 'Return material created successfully!');
    }

    public function show($id)
    {
        // Load return_material_items for the specific ReturnMaterial
        $returnMaterial = ReturnMaterial::with('returnMaterialItems')->findOrFail($id);
        
        return view('return-materials.show', compact('returnMaterial'));
    }
    

    public function edit($id)
    {
        $returnMaterial = ReturnMaterial::with('materials')->findOrFail($id);
        $drivers = Driver::all();
        $projects = Project::all();
        return view('return-materials.edit', compact('returnMaterial', 'drivers', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date_time' => 'required|date',
            'driver_id' => 'required|exists:drivers,id',
            'project_type' => 'required|in:general,project',
            'project_id' => 'nullable|exists:projects,id',
            'return_reason' => 'required|string|max:255',
            'materials' => 'required|array',
            'materials.*.quantity' => 'required|integer|min:0',
        ]);

        $returnMaterial = ReturnMaterial::findOrFail($id);
        $returnMaterial->update([
            'date_time' => Carbon::parse($request->date_time),
            'driver_id' => $request->driver_id,
            'project_type' => $request->project_type,
            'project_id' => $request->project_id,
            'return_reason' => $request->return_reason,
        ]);

        // Delete old items
        $returnMaterial->materials()->delete();

        foreach ($request->materials as $materialData) {
            if ($materialData['quantity'] > 0) {
                $returnMaterial->materials()->create([
                    'item_name' => $materialData['item_name'],
                    'category' => $materialData['category'],
                    'unit' => $materialData['unit'],
                    'quantity' => $materialData['quantity'],
                ]);
            }
        }

        return redirect()->route('return-materials.index')->with('success', 'Return material updated successfully!');
    }

    public function destroy($id)
    {
        $returnMaterial = ReturnMaterial::findOrFail($id);
        $returnMaterial->delete();

        return redirect()->route('return-materials.index')->with('success', 'Return material deleted successfully!');
    }

    public function loadMaterials(Request $request)
    {
        $projectId = $request->input('project_id');

        if (!$projectId) {
            return response()->json([]);
        }

        // Load outgoing materials for selected project
        $outgoingItems = OutgoingMaterialItem::whereHas('outgoingMaterial', function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->get(['item_name', 'category', 'unit', 'quantity']);

        return response()->json($outgoingItems);
    }
}
