<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/** One delivery log row. Never deleted, only voided. */
class PoReceiptEntry extends Model
{
    public const TYPES = ['store_receipt', 'direct_to_site', 'from_stock'];

    public const TYPE_LABELS = [
        'store_receipt' => 'Received at store',
        'direct_to_site' => 'Direct to site',
        'from_stock' => 'From store stock',
    ];

    protected $fillable = [
        'portal_order_id', 'portal_order_line_id', 'type', 'quantity', 'entry_date', 'vendor_dn_number',
        'site_receiver_name', 'dn_file_path', 'source_stock_item_id', 'stock_item_id', 'note', 'recorded_by',
        'voided_at', 'void_reason', 'voided_by', 'stock_issue_details', 'submission_uuid',
    ];

    protected $casts = [
        'quantity' => 'float',
        'entry_date' => 'date',
        'voided_at' => 'datetime',
        'stock_issue_details' => 'array',
    ];

    protected static function booted()
    {
        static::deleting(function () {
            throw new LogicException('Delivery entries cannot be deleted, only voided.');
        });
    }

    public function order()
    {
        return $this->belongsTo(PortalOrder::class, 'portal_order_id');
    }

    public function line()
    {
        return $this->belongsTo(PortalOrderLine::class, 'portal_order_line_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }

    public function sourceStock()
    {
        return $this->belongsTo(IncomingMaterialItem::class, 'source_stock_item_id')->withTrashed();
    }

    public function getIsVoidedAttribute(): bool
    {
        return $this->voided_at !== null;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    /** Used in the log and the portal payload: which stock row a from_stock entry took from. */
    public function stockLabel(): ?string
    {
        if ($this->type !== 'from_stock' || !$this->sourceStock) {
            return null;
        }
        $s = $this->sourceStock;

        return trim($s->item_name . ' / ' . $s->category . ' (' . $s->unit . ')');
    }
}
