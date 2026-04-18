<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp',
        'purpose',
        'expires_at',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the user that owns the OTP.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
