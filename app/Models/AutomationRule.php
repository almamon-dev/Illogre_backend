<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'conditions',
        'actions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
    ];
}
