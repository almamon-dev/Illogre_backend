<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'billing_period',
        'trial_days',
        'features',
        'is_active',
        'is_popular',
        'order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
