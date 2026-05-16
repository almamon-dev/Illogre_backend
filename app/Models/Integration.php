<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'settings',
        'status',
        'last_synced_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'settings' => 'encrypted:array',
        'expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the integration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
