<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_pemesanan',
        'jenis_pemesanan',
        'nama_pemesan',
        'nomor_telepon',
        'alamat',
        'berat_laundry',
        'total_harga',
        'status',
        'tanggal_pemesanan',
        'tanggal_pengambilan',
        'laundry_id',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
