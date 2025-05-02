<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_log_id',
        'status',
        'status_date',
        'is_done',
    ];

    protected $casts = [
        'status_date' => 'date',
        'is_done' => 'boolean',
        'created_at' => 'datetime',
        // 'updated_at' tidak ada di fillable/skema? Jika ada, tambahkan:
        // 'updated_at' => 'datetime',
    ];

    // Relasi ke ReceivingLog (Asumsi sudah ada)
    public function receivingLog(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ReceivingLog::class);
    }

    // Relasi ke User (created_by) (Asumsi sudah ada)
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * Accessor untuk mendapatkan status yang sudah diformat.
     * Nama method: getNamaAttribute() -> dipanggil sebagai $model->nama
     * Kita buat: getFormattedStatusWithDetailsAttribute() -> dipanggil $model->formatted_status_with_details
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function formattedStatusWithDetails(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                // Ambil nilai status dan tanggal dari array attributes
                $statusCode = $attributes['status'] ?? null;
                $statusDate = isset($attributes['status_date']) ? Carbon::parse($attributes['status_date']) : null;
                $isDone = $attributes['is_done'] ?? false;

                // Mapping kode status ke teks (sesuaikan teksnya sesuai kebutuhan)
                $statusLabel = match ($statusCode) {
                    1 => 'COA', // Contoh Teks untuk status 1
                    2 => '103', // Contoh Teks untuk status 2
                    3 => '105', // Contoh Teks untuk status 3
                    4 => '122', // Contoh Teks untuk status 4
                    5 => '161', // Contoh Teks untuk status 5
                    6 => '109', // Contoh Teks untuk status 6
                    7 => '501', // Contoh Teks untuk status 7
                    8 => '551', // Contoh Teks untuk status 8
                    default => 'Status Tidak Diketahui', // Fallback jika kode tidak ada di map
                };

                // Format tanggal (contoh: 29 Apr 2025)
                $formattedDate = $statusDate ? $statusDate->translatedFormat('d M Y') : 'Tanggal Tidak Ada';

                // Tambahkan ", Done" jika is_done = true
                $doneText = $isDone ? ', Done' : '';

                // Gabungkan semua bagian
                return "{$statusLabel}{$doneText} ({$formattedDate})";
            },
        );
    }

    public function receivingLogs(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ReceivingLog::class, 'receiving_log_id', 'id');
    }
}
