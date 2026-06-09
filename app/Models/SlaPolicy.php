<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'first_response_time_minutes',
        'resolution_time_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
