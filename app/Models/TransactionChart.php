<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_transactions',
        'total_income',
    ];

    protected $casts = [
        'total_transactions' => 'integer',
        'total_income' => 'integer',
    ];
}
