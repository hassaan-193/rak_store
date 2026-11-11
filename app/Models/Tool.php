<?php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tool extends Model
{
    use RecyclesOnDelete;
    use HasFactory;

    protected $fillable = [
        'item_name',
        'category',
        'item_code',
        'quantity',
    ];
}
