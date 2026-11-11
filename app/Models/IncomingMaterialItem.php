<?php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingMaterialItem extends Model
{
    use RecyclesOnDelete;
    use HasFactory;

    protected $fillable = [
        'incoming_material_id',
        'item_name',
        'category',
        'brand',
        'unit',
        'quantity',
    ];

    public function incomingMaterial()
    {
        return $this->belongsTo(IncomingMaterial::class);
    }

    // public function transferToGeneral($quantity)
    // {
    //     // Step 1: Decrease quantity from current item
    //     $this->quantity -= $quantity;

    //     // Save or delete the current item
    //     if ($this->quantity <= 0) {
    //         $this->delete(); // Delete item from project
    //     } else {
    //         $this->save(); // Update remaining quantity
    //     }

    //     // Step 2: Create a new entry in General category
    //     $generalMaterial = IncomingMaterial::firstOrCreate([
    //         'project_type' => 'general',
    //         'store_location' => $this->incomingMaterial->store_location,
    //         'dn_number' => $this->incomingMaterial->dn_number,
    //     ], [
    //         'date_time' => now(),
    //         'source_type' => $this->incomingMaterial->source_type,
    //         'driver_id' => $this->incomingMaterial->driver_id,
    //         'supplier_id' => $this->incomingMaterial->supplier_id,
    //         'engineer_name' => $this->incomingMaterial->engineer_name,
    //     ]);

    //     $generalMaterial->items()->create([
    //         'item_name' => $this->item_name,
    //         'category' => $this->category,
    //         'brand' => $this->brand,
    //         'unit' => $this->unit,
    //         'quantity' => $quantity,
    //     ]);

    //     // Step 3: If original incomingMaterial has no more items, delete it
    //     if ($this->incomingMaterial->items()->count() === 0) {
    //         $this->incomingMaterial->delete();
    //     }
    // }


}
