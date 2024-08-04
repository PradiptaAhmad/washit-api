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
        'laundry_id',
        'user_id',
    ];

    public function transactions(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }
}
