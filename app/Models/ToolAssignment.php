<?php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolAssignment extends Model
{
    use RecyclesOnDelete;
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'date_time',
        'item_name',
        'category',
        'item_code',
        'worker_name', 
        'project_name',
        'quantity_issued',
        'purpose',
        'condition_before_issue',
        'assignment_date', 
    ];
    
    

    // Relationship with Tool model
    public function tool()
    {
        return $this->belongsTo(Tool::class)->withTrashed();
    }

    // Relationship with Worker (User) model
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id')->withTrashed();
    }

    public function returns()
{
    return $this->hasMany(ToolReturn::class);
}

}
