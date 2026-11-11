<?php
namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\OutgoingMaterial;
use App\Models\ReturnMaterial;
use App\Models\ToolReturn;


class DashboardController extends Controller
{
    public function index()
    {
        // Fetch the counts of records
        $incomingMaterialCount = IncomingMaterial::count();
        $outgoingMaterialCount = OutgoingMaterial::count();
        $returnableMaterialCount = ReturnMaterial::count();
        $returnableToolCount = ToolReturn::count();


        // For outgoing material, if needed, you can calculate the percentage of outgoing materials
        $totalMaterials = OutgoingMaterial::count();
        $outgoingMaterialPercentage = $outgoingMaterialCount ;

        










        // Return the data to the view
        return view('dashboard', compact(
            'incomingMaterialCount',
            'outgoingMaterialCount',
            'outgoingMaterialPercentage',
            'returnableMaterialCount',
            'returnableToolCount'
        ));
    }
}
