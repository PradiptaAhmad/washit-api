<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'street',
        'type',
        'notes',
        'is_primary',
        'user_id',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
