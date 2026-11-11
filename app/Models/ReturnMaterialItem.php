<?php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMaterialItem extends Model
{
    use RecyclesOnDelete;
    use HasFactory;

    protected $fillable = [
        'return_material_id', 'item_name', 'category', 'unit', 'quantity'
    ];

    public function returnMaterial()
    {
        return $this->belongsTo(ReturnMaterial::class);
    }
}
