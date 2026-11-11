<?php

namespace App\Http\Controllers;
use App\Models\ToolAssignment;

use Illuminate\Http\Request;
use App\Models\Tool;


class ToolController extends Controller
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
        $tools = Tool::all();
        return view('tools.index', compact('tools'));
    }
    


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('tools.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'tools' => 'required|array|min:1',
            'tools.*.item_name' => 'required|string|max:255',
            'tools.*.category' => 'required|string|max:255',
            'tools.*.item_code' => 'required|string|max:255|distinct',
            'tools.*.quantity' => 'required|integer|min:1',
        ]);
    
        foreach ($request->tools as $toolData) {
            Tool::create([
                'item_name' => $toolData['item_name'],
                'category' => $toolData['category'],
                'item_code' => $toolData['item_code'],
                'quantity' => $toolData['quantity'],
            ]);
        }
    
        return redirect()->route('tools.index')->with('success', 'Tools added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tool = Tool::findOrFail($id);
        return view('tools.show', compact('tool'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tool = Tool::findOrFail($id);
        return view('tools.edit', compact('tool'));
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
        $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'item_code' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);
    
        $tool = Tool::findOrFail($id);
        $tool->update($request->all());
    
        return redirect()->route('tools.index')->with('success', 'Tool updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */

    public function destroy($id)
    {
        $tool = Tool::findOrFail($id);
        $tool->delete();
    
        return redirect()->route('tools.index')->with('success', 'Tool deleted successfully.');
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

    // Method for exporting data to Excel
    public function exportToExcel($data, array $headers, string $filename = 'export.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers in Excel file
        $sheet->fromArray($headers, null, 'A1');

        // Set data in Excel file starting from row 2
        $sheet->fromArray($data, null, 'A2');

        // Write the Excel file
        $writer = new Xlsx($spreadsheet);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Method for exporting IncomingMaterial data
    public function export(Request $request)
    {
        // Fetch data from the IncomingMaterial model (with relationships)
        $data = IncomingMaterial::with(['supplier', 'driver'])->get()->map(function ($item) {
            return [
                'Date / Time' => $item->date_time,
                'Order No' => $item->dn_number,
                'Project' => $item->project_name,
                'Supplier' => $item->supplier->name ?? '-',
                'Driver' => $item->driver->name ?? '-',
                'Storage Location' => $item->store_location,
            ];
        });

        // Define headers dynamically based on the data structure
        $headers = ['Date / Time', 'Order No', 'Project', 'Supplier', 'Driver', 'Storage Location'];

        // Check the request for the export type (CSV or Excel)
        if ($request->export_type == 'csv') {
            return $this->exportToCSV($data, $headers, 'incoming_materials.csv');
        } else {
            return $this->exportToExcel($data, $headers, 'incoming_materials.xlsx');
        }
    }



    public function availability()
{
    $tools = Tool::all()->map(function ($tool) {
        $assignedQuantity = ToolAssignment::where('tool_id', $tool->id)->sum('quantity_issued');

        return [
            'item_name' => $tool->item_name,
            'category' => $tool->category,
            'item_code' => $tool->item_code,
            'total_quantity' => $tool->quantity + $assignedQuantity,
            'available_quantity' => $tool->quantity,
            'assigned_quantity' => $assignedQuantity,
            'assignments' => ToolAssignment::where('tool_id', $tool->id)->get()
        ];
    });

    return view('tools.availability', compact('tools'));
}

}
