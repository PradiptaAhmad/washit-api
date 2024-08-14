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
        'berat_laundry',
        'alamat',
        'total_harga',
        'catatan',
        'status',
        'tanggal_pengambilan',
        'tanggal_estimasi',
        'metode_pembayaran',
        'laundry_service',
        'laundry_description',
        'laundry_price',
        'user_id'
    ];

    protected $casts = [
        'total_harga' => 'integer',
        'berat_laundry' => 'integer',
        "laundry_id" => 'integer',
        "laundry_price" => 'integer',
        "user_id" => 'integer',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function status() {
        return $this->hasMany(OrderStatus::class);
    }

    public function transaction() {
        return $this->hasOne(Transaction::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
