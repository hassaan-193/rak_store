<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolReturn;
use App\Models\ToolAssignment;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ToolReturnController extends Controller
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
        // Fetch tool returns with related tool and tool assignment data (without the worker relationship)
        $toolReturns = ToolReturn::with(['toolAssignment.tool'])->get();
    
        // Pass the tool returns to the view
        return view('tool-returns.index', compact('toolReturns'));
    }
    

    public function create()
    {
        $assignments = ToolAssignment::all(); // Eager loading not needed
        return view('tool-returns.create', compact('assignments'));
    }

    public function store(Request $request)
    {
        \Log::info('Request Data:', $request->all());
    
        $request->validate([
            'assignment_id' => 'required|exists:tool_assignments,id',
            'return_date' => 'required|date',
            'quantity_returned' => 'required|integer|min:1',
            'return_condition' => 'required|string|max:255',
        ]);
    
        \Log::info('Validation passed.');
    
        // Find the ToolAssignment by ID
        $assignment = ToolAssignment::with('tool')->find($request->assignment_id);
    
        if (!$assignment) {
            \Log::error('Tool Assignment not found', ['tool_assignment_id' => $request->assignment_id]);
            return redirect()->back()->withErrors(['error' => 'Tool assignment not found.']);
        }
    
        // Check if returned quantity exceeds the issued quantity
        if ($request->quantity_returned > $assignment->quantity_issued) {
            return redirect()->back()->withErrors(['quantity_returned' => 'Returned quantity cannot exceed issued quantity.']);
        }
    
        // Create the ToolReturn record
        $toolReturn = ToolReturn::create([
            'tool_assignment_id' => $assignment->id,
            'return_date' => $request->return_date,
            'quantity_returned' => $request->quantity_returned,
            'return_condition' => $request->return_condition,
        ]);
    
        \Log::info('Tool return created:', $toolReturn->toArray());
    
        // Adjust quantities after return
        $this->adjustQuantitiesAfterReturn($assignment, $request->quantity_returned);
    
        return redirect()->route('tool-returns.index')->with('success', 'Tool returned successfully.');
    }
    

    public function show($id)
    {
        $toolReturn = ToolReturn::with(['toolAssignment.tool'])->findOrFail($id);
        return view('tool-returns.show', compact('toolReturn'));
    }

    public function destroy($id)
    {
        $toolReturn = ToolReturn::findOrFail($id);
        $assignment = $toolReturn->toolAssignment;

        $this->revertQuantitiesAfterReturnDeletion($assignment, $toolReturn->quantity_returned);

        $toolReturn->delete();

        return redirect()->route('tool-returns.index')->with('success', 'Tool return deleted and quantities reverted.');
    }

    // PRIVATE HELPERS

    /**
     * Adjust tool and assignment quantities when a return is created
     */
    private function adjustQuantitiesAfterReturn(ToolAssignment $assignment, int $quantityReturned)
    {
        $tool = $assignment->tool;

        $tool->increment('quantity', $quantityReturned);
        $assignment->decrement('quantity_issued', $quantityReturned);

        \Log::info('Quantities after return:', [
            'tool_id' => $tool->id,
            'new_tool_quantity' => $tool->quantity,
            'assignment_id' => $assignment->id,
            'new_quantity_issued' => $assignment->quantity_issued
        ]);
    }

    /**
     * Revert tool and assignment quantities when a return is deleted
     */
    private function revertQuantitiesAfterReturnDeletion(ToolAssignment $assignment, int $quantityReturned)
    {
        $tool = $assignment->tool;

        $tool->decrement('quantity', $quantityReturned);
        $assignment->increment('quantity_issued', $quantityReturned);

        \Log::info('Quantities reverted after return deletion:', [
            'tool_id' => $tool->id,
            'new_tool_quantity' => $tool->quantity,
            'assignment_id' => $assignment->id,
            'new_quantity_issued' => $assignment->quantity_issued
        ]);
    }



         public function logIndex()
        {
            $toolReturns = ToolReturn::with(['toolAssignment.tool', 'toolAssignment.worker'])->get();
            return view('tool-returns.log', compact('toolReturns'));
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
            // Fetch data from the ToolReturn model (with relationship)
            $data = ToolReturn::with('toolAssignment')->get()->map(function ($item) {
                return [
                    'Return Date'        => $item->return_date,
                    'Tool Assignment ID' => $item->tool_assignment_id,
                    'Item Name'          => $item->toolAssignment->item_name ?? '-',
                    'Quantity Returned'  => $item->quantity_returned,
                    'Return Condition'   => $item->return_condition,
                ];
            });
        
            // Define headers dynamically based on the data structure
            $headers = [
                'Return Date',
                'Tool Assignment ID',
                'Item Name',
                'Quantity Returned',
                'Return Condition',
            ];
        
            // Check the request for the export type (CSV or Excel)
            if ($request->export_type == 'csv') {
                return $this->exportToCSV($data, $headers, 'tool_returns.csv');
            } else {
                return $this->exportToExcel($data, $headers, 'tool_returns.xlsx');
            }
        }
        

}
