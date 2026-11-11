<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class PortalOrderLine extends Model
{
    protected $fillable = ['portal_order_id', 'material_name', 'item_code', 'unit', 'quantity_ordered'];

    protected $casts = ['quantity_ordered' => 'float'];

    protected static function booted()
    {
        // A line that has any delivery entry (even voided) is part of the record.
        static::deleting(function (PortalOrderLine $line) {
            if ($line->entries()->exists()) {
                throw new LogicException('A line with delivery entries cannot be deleted.');
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(PortalOrder::class, 'portal_order_id');
    }

    public function entries()
    {
        return $this->hasMany(PoReceiptEntry::class, 'portal_order_line_id');
    }

    /** Same name + item code means the same line when the portal re-sends items. */
    public static function keyFor($name, $code): string
    {
        return mb_strtolower(trim((string) $name)) . '|' . mb_strtolower(trim((string) $code));
    }
}
