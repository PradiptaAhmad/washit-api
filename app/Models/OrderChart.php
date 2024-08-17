<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_orders',
    ];
}
