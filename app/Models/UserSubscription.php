<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'started_at',
        'expires_at',
        'status',
        'is_trial',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_trial' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }


    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function isExpired()
    {
        return $this->expires_at < now();
    }
}
