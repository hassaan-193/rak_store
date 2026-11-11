<?php

namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use RecyclesOnDelete;
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];


    public function incomingMaterials()
    {
        return $this->hasMany(IncomingMaterial::class);
    }
}
