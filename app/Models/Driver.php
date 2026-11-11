<?php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use RecyclesOnDelete;
    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function incomingMaterials()
    {
        return $this->hasMany(IncomingMaterial::class, 'fts_driver_id');
    }
}
