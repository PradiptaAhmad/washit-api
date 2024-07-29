<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;

    protected $fillable = ['nama_laundry', 'harga', 'estimasi_waktu'];
    protected $casts = [
        'nama_laundry' => 'string',
        'deskripsi' => 'string',
        'harga' => 'integer',
        'estimasi_waktu' => 'integer'
    ];
}
