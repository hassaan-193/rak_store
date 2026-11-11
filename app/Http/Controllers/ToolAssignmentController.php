<?php
namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\User; 
use App\Models\Driver;  
use App\Models\Supplier;  
use App\Models\Project; 

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ToolAssignmentController extends Controller
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
        $toolAssignments = ToolAssignment::with(['tool', 'worker'])->get();
        return view('tool-assignments.index', compact('toolAssignments'));
    }

    public function create()
    {
        $tools = Tool::all();
        $projects = Project::all(); // Fetch projects
        return view('tool-assignments.create', compact('tools', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tool_id' => 'required|exists:tools,id',
            'date_time' => 'required|date',
            'worker_name' => 'required|string|max:255', 
            'project_name' => 'required|string|max:255',
            'quantity_issued' => 'required|integer|min:1',
            'purpose' => 'required|string|max:255',
            'condition_before_issue' => 'required|string|max:255',
        ]);
    
        $tool = Tool::findOrFail($request->tool_id);
    
        if ($tool->quantity < $request->quantity_issued) {
            return redirect()->back()->withErrors(['quantity_issued' => 'Not enough quantity available.']);
        }
    
        ToolAssignment::create([
            'tool_id' => $request->tool_id,
            'date_time' => $request->date_time,
            'item_name' => $tool->item_name,
            'category' => $tool->category,
            'item_code' => $tool->item_code,
            'worker_name' => $request->worker_name, // Store name directly
            'project_name' => $request->project_name,
            'quantity_issued' => $request->quantity_issued,
            'assignment_date' => $request->date_time,  // 👈 add this line
            'purpose' => $request->purpose,
            'condition_before_issue' => $request->condition_before_issue,
        ]);
        
    
        // Decrease quantity
        $tool->decrement('quantity', $request->quantity_issued);
    
        return redirect()->route('tool-assignments.index')->with('success', 'Tool assigned successfully.');
    }
    
    public function show($id)
{
    $assignment = ToolAssignment::with('worker')->findOrFail($id);
    return view('tool-assignments.show', compact('assignment'));
}
public function edit($id)
{
    $assignment = ToolAssignment::findOrFail($id);
$projectList = Project::pluck('name', 'id')->toArray();

    $tools = Tool::all();
    return view('tool-assignments.edit', compact('assignment', 'tools', 'projectList'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'tool_id' => 'required|exists:tools,id',
        'worker_name' => 'required|string|max:255',
        'project_name' => 'required|string',
        'quantity_issued' => 'required|integer|min:1',
        'purpose' => 'required|string',
        'condition_before_issue' => 'required|string',
        'date_time' => 'required|date',
    ]);

    $assignment = ToolAssignment::findOrFail($id);
    $oldTool = Tool::where('item_code', $assignment->item_code)->first();
    $newTool = Tool::findOrFail($request->tool_id);

    $oldQuantity = $assignment->quantity_issued;
    $newQuantity = $request->quantity_issued;

    if ($oldTool->id == $newTool->id) {
        $difference = $newQuantity - $oldQuantity;

        if ($difference > 0 && $newTool->quantity < $difference) {
            return back()->withErrors(['quantity_issued' => 'Not enough quantity in selected tool.']);
        }

        if ($difference > 0) {
            $newTool->decrement('quantity', $difference);
        } elseif ($difference < 0) {
            $newTool->increment('quantity', abs($difference));
        }
    } else {
        $oldTool->increment('quantity', $oldQuantity);

        if ($newTool->quantity < $newQuantity) {
            return back()->withErrors(['quantity_issued' => 'Not enough quantity in selected tool.']);
        }

        $newTool->decrement('quantity', $newQuantity);
    }

    $assignment->update([
        'date_time' => $request->date_time,
        'tool_id' => $request->tool_id,
        'item_name' => $newTool->item_name,
        'category' => $newTool->category,
        'item_code' => $newTool->item_code,
        'worker_name' => $request->worker_name,
        'project_name' => $request->project_name,
        'quantity_issued' => $newQuantity,
        'purpose' => $request->purpose,
        'assignment_date' => $request->date_time,  // 👈 add this line
        'condition_before_issue' => $request->condition_before_issue,
    ]);

    return redirect()->route('tool-assignments.index')->with('success', 'Tool assignment updated successfully.');
}


public function destroy($id)
{
    $assignment = ToolAssignment::findOrFail($id);
    
    // Find the tool by item_code (or use tool_id if you're saving it now)
    $tool = Tool::where('item_code', $assignment->item_code)->first();

    if ($tool) {
        // Return the quantity
        $tool->increment('quantity', $assignment->quantity_issued);
    }

    $assignment->delete();

    return redirect()->route('tool-assignments.index')->with('success', 'Tool assignment deleted and quantity restored.');
}
        // Method for exporting data to CSV
        public function exportToCSV($data, array $headers, string $filename = 'export.csv')
        {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers); // Add headers
    
            foreach ($data as $row) {
                fputcsv($handle, (array) $row); // Ensure row is an array
            }
    
            // Set headers for the response
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            // Output the CSV to the browser
            fpassthru($handle);
            exit;
        }
    

    
        
        public function export(Request $request)
        {
            // Fetch data from the ToolAssignment model (with relationships)
            $data = ToolAssignment::with(['tool', 'worker'])->get()->map(function ($item) {
                return [
                    'Date / Time'             => $item->date_time,
                    'Tool'                    => $item->tool->name ?? '-',
                    'Item Name'               => $item->item_name,
                    'Category'                => $item->category,
                    'Item Code'               => $item->item_code,
                    'Worker'                  => $item->worker->name ?? '-',
                    'Project'                 => $item->project_name,
                    'Quantity Issued'         => $item->quantity_issued,
                    'Purpose'                 => $item->purpose,
                    'Condition Before Issue'  => $item->condition_before_issue,
                ];
            });
        
            // Define headers dynamically based on the data structure
            $headers = [
                'Date / Time',
                'Tool',
                'Item Name',
                'Category',
                'Item Code',
                'Worker',
                'Project',
                'Quantity Issued',
                'Purpose',
                'Assignment Date',
                'Condition Before Issue',
            ];
        
            // Check the request for the export type (CSV or Excel)
            if ($request->export_type == 'csv') {
                return $this->exportToCSV($data, $headers, 'tool_assignments.csv');
            } else {
                return $this->exportToExcel($data, $headers, 'tool_assignments.xlsx');
            }
        }
        

    
}
