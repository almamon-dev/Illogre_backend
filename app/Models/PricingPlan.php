<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stripe_product_id',
        'stripe_price_id',
        'price',
        'billing_period',
        'trial_days',
        'is_active',
        'is_popular',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function planFeatures()
    {
        return $this->hasMany(PricingPlanFeature::class);
    }
}
