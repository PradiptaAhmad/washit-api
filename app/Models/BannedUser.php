<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'banned_at',
        'unbanned_at',
        'reason',
    ];
}
