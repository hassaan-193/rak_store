<?php


namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMaterial extends Model
{
    use RecyclesOnDelete;
    protected $recycleChildren = ['materials'];
    use HasFactory;

    protected $fillable = ['date_time', 'driver_id', 'project_type', 'project_id', 'return_reason'];

    public function driver()
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function materials()
    {
        return $this->hasMany(ReturnMaterialItem::class); // Assuming the material is stored in a pivot table or related model
    }
    // ReturnMaterial.php

public function returnMaterialItems()
{
    return $this->hasMany(ReturnMaterialItem::class);
}

}
