<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'no_pemesanan',
        'jenis_pemesanan',
        'nama_pemesan',
        'nomor_telepon',
        'alamat',
        'metode_pembayaran',
        'berat_laundry',
        'total_harga',
        'status',
        'tanggal_pengambilan',
        'tanggal_estimasi',
        'catatan',
        'laundry_service',
        'laundry_description',
        'laundry_price',
        'user_id',
    ];

    protected $casts = [
        'total_harga' => 'integer',
        'berat_laundry' => 'integer',
        "laundry_id" => 'integer',
        "user_id" => 'integer',
    ];

    public function transactions(): BelongsTo
    {
        return $this->belongsTo(TransactionHistory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
