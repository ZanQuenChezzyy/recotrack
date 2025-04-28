<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_log_id',
        'status',
        'status_date',
        'is_done',
    ];
    public function receivingLogs(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ReceivingLog::class, 'receiving_log_id', 'id');
    }
}
