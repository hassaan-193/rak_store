<?php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingMaterial extends Model
{
    use RecyclesOnDelete;
    protected $recycleChildren = ['items'];
    use HasFactory;

    protected $fillable = [
        'date_time',
        'source_type',
        'driver_id',
        'supplier_id',
        'project_type',
        'project_id',
        'engineer_name',
        'store_location',
        'dn_number',
    ];

    public function items()
    {
        return $this->hasMany(IncomingMaterialItem::class);
    }
    public function project()
{
    return $this->belongsTo(Project::class)->withTrashed();
}

public function driver()
{
    return $this->belongsTo(Driver::class)->withTrashed();
}

public function supplier()
{
    return $this->belongsTo(Supplier::class)->withTrashed();
}
public function materials()
{
    return $this->hasMany(IncomingMaterialItem::class, 'incoming_material_id');
}


}
