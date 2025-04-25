<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type_id',
        'description',
    ];

    public function types(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Type::class, 'type_id', 'id');
    }
    public function receivingLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ReceivingLog::class);
    }
}
