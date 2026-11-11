<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingMaterialMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'outgoing_material_id', // Reference to the Outgoing Material
        'material_id',          // Reference to the material
        'quantity',             // Quantity being taken out
    ];

    /**
     * Define the relationship with the OutgoingMaterial model.
     */
    public function outgoingMaterial()
    {
        return $this->belongsTo(OutgoingMaterial::class);
    }

    /**
     * Define the relationship with the Material model.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
