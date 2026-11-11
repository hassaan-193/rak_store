<?php

namespace App\Http\Controllers;
use App\Models\IncomingMaterial;
use App\Models\Supplier;
use App\Models\Driver;
use App\Models\Project;
use Illuminate\Http\Request;

class IncomingMaterialController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:show')->only(['index', 'show']);
        $this->middleware('permission:create')->only(['create', 'store']);
        $this->middleware('permission:edit')->only(['edit', 'update']);
        $this->middleware('permission:delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Step 1: Fetch all incoming materials with their related items
        $incomingMaterials = IncomingMaterial::with('items')->latest()->get();
    
        // Step 2: Pass data to the view
        return view('incoming-materials.index', compact('incomingMaterials'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::pluck('name', 'id');
        $drivers = Driver::pluck('name', 'id');
        $projects = Project::all();

        return view('incoming-materials.create', compact('suppliers', 'drivers', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Step 1: Validate the main form
        $validatedData = $request->validate([
            'date_time' => 'required|date',
            'source_type' => 'required|in:driver,supplier',
            'project_type' => 'required|in:general,project',
            'driver_id' => 'nullable|exists:drivers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'engineer_name' => 'required|string|max:255',
            'store_location' => 'required|string|max:255',
            'dn_number' => 'required|string|max:255',
    
            'materials' => 'required|array',
            'materials.*.item_name' => 'required|string|max:255',
            'materials.*.category' => 'required|string|max:255',
            'materials.*.brand' => 'nullable|string|max:255',
            'materials.*.unit' => 'required|string|max:255',
            'materials.*.quantity' => 'required|numeric|min:0',
        ]);
    
        // Step 2: Create Incoming Material record
        $incomingMaterial = IncomingMaterial::create([
            'date_time' => $validatedData['date_time'],
            'source_type' => $validatedData['source_type'],
            'driver_id' => $validatedData['source_type'] == 'driver' ? $validatedData['driver_id'] : null,
            'supplier_id' => $validatedData['source_type'] == 'supplier' ? $validatedData['supplier_id'] : null,
            'project_type' => $validatedData['project_type'],
            'project_id' => $validatedData['project_type'] == 'project' ? $validatedData['project_id'] : null,
            'engineer_name' => $validatedData['engineer_name'],
            'store_location' => $validatedData['store_location'],
            'dn_number' => $validatedData['dn_number'],
        ]);
    
        // Step 3: Create Material Items
        foreach ($validatedData['materials'] as $materialData) {
            $incomingMaterial->items()->create([
                'item_name' => $materialData['item_name'],
                'category' => $materialData['category'],
                'brand' => $materialData['brand'] ?? null,
                'unit' => $materialData['unit'],
                'quantity' => $materialData['quantity'],
            ]);
        }
    
        return redirect()->route('incoming-materials.index')
            ->with('success', 'Incoming Material created successfully.');
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch the incoming material with its associated items
        $incomingMaterial = IncomingMaterial::with('items')->findOrFail($id);
    
        // Pass the data to the view
        return view('incoming-materials.show', compact('incomingMaterial'));
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $incomingMaterial = IncomingMaterial::with('materials')->findOrFail($id);
    
        $projects = Project::all();
        $drivers = Driver::pluck('name', 'id');
        $suppliers = Supplier::pluck('name', 'id');
    
        return view('incoming-materials.edit', compact('incomingMaterial', 'projects', 'drivers', 'suppliers'));
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'date_time' => 'required|date',
            'source_type' => 'required|in:driver,supplier',
            'driver_id' => 'nullable|exists:drivers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'project_type' => 'required|in:general,project',
            'project_id' => 'nullable|exists:projects,id',
            'engineer_name' => 'required|string|max:255',
            'store_location' => 'required|string|max:255',
            'dn_number' => 'required|string|max:255',
            'materials' => 'required|array',
            'materials.*.item_name' => 'required|string|max:255',
            'materials.*.category' => 'required|string|max:255',
            'materials.*.brand' => 'nullable|string|max:255',
            'materials.*.unit' => 'required|string|max:255',
            'materials.*.quantity' => 'required|numeric|min:0',
        ]);
    
        $incomingMaterial = IncomingMaterial::findOrFail($id);
    
        $incomingMaterial->update([
            'date_time' => $validatedData['date_time'],
            'source_type' => $validatedData['source_type'],
            'driver_id' => $validatedData['source_type'] == 'driver' ? $validatedData['driver_id'] : null,
            'supplier_id' => $validatedData['source_type'] == 'supplier' ? $validatedData['supplier_id'] : null,
            'project_type' => $validatedData['project_type'],
            'project_id' => $validatedData['project_type'] == 'project' ? $validatedData['project_id'] : null,
            'engineer_name' => $validatedData['engineer_name'],
            'store_location' => $validatedData['store_location'],
            'dn_number' => $validatedData['dn_number'],
        ]);
    
        // Delete old materials
        $incomingMaterial->materials()->delete();
    
        // Insert new materials
        foreach ($validatedData['materials'] as $materialData) {
            $incomingMaterial->materials()->create($materialData);
        }
    
        return redirect()->route('incoming-materials.index')->with('success', 'Incoming material updated successfully.');
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomingMaterial $incomingMaterial)
    {
        // Moves the receipt and its items to the Recycle Bin (items are recycled with it)
        $incomingMaterial->delete();
    
        return redirect()->route('incoming-materials.index')->with('success', 'Incoming Material deleted successfully.');
    }
    


    public function filterByProject(Request $request)
    {
        $projects = Project::all();
        $query = IncomingMaterial::with(['items', 'project','supplier',   
        'driver']);
    
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id)
                  ->where('project_type', 'project');
        } elseif ($request->get('type') === 'general') {
            $query->where('project_type', 'general');
        }
    
        $incomingMaterials = $query->orderBy('date_time', 'desc')->get();
    
        return view('incoming-materials.filter-by-project', compact('incomingMaterials', 'projects'));
    }
    

    

}
