<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'monitoring_date',
        'quantity',
        'uom_id',
        'stage',
        'qc_date',
        'received_date',
        'notes',
    ];

    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\PurchaseOrder::class, 'purchase_order_id', 'id');
    }
    public function statusHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StatusHistory::class);
    }
    public function uoms(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Uom::class, 'uom_id', 'id');
    }
}
