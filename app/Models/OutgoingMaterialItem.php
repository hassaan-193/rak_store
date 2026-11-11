<?php
// app/Models/OutgoingMaterialItem.php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingMaterialItem extends Model
{
    use RecyclesOnDelete;
    use HasFactory;

    protected $fillable = [
        'outgoing_material_id', 'item_name', 'category', 'unit', 'quantity', 'stock_allocations', 'allocations_reversed_at'
    ];

    protected $casts = [
        'stock_allocations' => 'array',
        'allocations_reversed_at' => 'datetime',
    ];

    /**
     * Get the outgoing material that owns the item.
     */
    public function outgoingMaterial()
    {
        return $this->belongsTo(OutgoingMaterial::class);
    }
    public function material()
{
    return $this->belongsTo(Material::class);
}

}
