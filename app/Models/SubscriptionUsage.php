<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUsage extends Model
{
    protected $fillable = [
        'user_id',
        'feature_name',
        'used_count',
        'last_reset_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
