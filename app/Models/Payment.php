<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'external_payment_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }
}
