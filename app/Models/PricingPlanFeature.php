<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlanFeature extends Model
{
    protected $fillable = [
        'pricing_plan_id',
        'name',
        'value',
        'is_limit',
    ];

    public function plan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }
}
