<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'status_code',
        'status_description',
        'order_id'
    ];

    protected $casts = [
        'status' => 'string',
        'status_code' => 'integer',
        'status_description' => 'string',
        'order_id' => 'integer'
    ];
}
