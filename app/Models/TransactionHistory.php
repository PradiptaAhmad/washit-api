<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'payment_type',
        'external_id',
        'payment_method',
        'status',
        'amount',
        'payment_channel',
        'description',
        'payment_id',
        'paid_at',
        'history_id'
    ];
}
