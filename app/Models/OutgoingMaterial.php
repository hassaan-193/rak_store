<?php
// app/Models/OutgoingMaterial.php
namespace App\Models;

use App\Models\Concerns\RecyclesOnDelete;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingMaterial extends Model
{
    use RecyclesOnDelete;
    protected $recycleChildren = ['items'];
    use HasFactory;

    protected $fillable = [
        'date_time',
        'driver_id',
        'project_type',
        'project_id',
        'engineer_name',
        'dn_number',
        'site',
        'status',
        'created_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'approval_comment',
    ];

    /**
     * Get the items for the outgoing material.
     */
    public function items()
    {
        return $this->hasMany(OutgoingMaterialItem::class);
    }

    /**
     * Get the driver associated with the outgoing material.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    /**
     * Get the project associated with the outgoing material.
     */
    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    /**
     * Get the user who created this request.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }


    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by')->withTrashed();
    }
public function isPending()
{
    return $this->status === 'pending';
}

public function isApproved()
{
    return $this->status === 'approved';
}

public function isRejected()
{
    return $this->status === 'rejected';
}

}
