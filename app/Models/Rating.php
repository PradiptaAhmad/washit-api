<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating',
        'review',
        'history_id',
        'user_id'
    ];

    protected $casts = [
        'rating' => 'double'
    ];


    public function user()
    {
        return $this->belongsTo(User::class)->first();
    }
}
