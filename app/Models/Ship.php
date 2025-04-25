<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class);
    }
}
