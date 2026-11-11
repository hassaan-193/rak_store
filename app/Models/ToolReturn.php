<?php

namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolReturn extends Model
{
    use RecyclesOnDelete;
    use HasFactory;
    protected $table = 'tool_returns'; 
    protected $fillable = [
        'tool_assignment_id', 'return_date', 'quantity_returned', 'return_condition',
    ];
    public function toolAssignment()
{
    return $this->belongsTo(ToolAssignment::class)->withTrashed();
}

}
