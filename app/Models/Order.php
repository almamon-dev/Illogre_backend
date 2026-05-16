<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'customer_id',
        'shopify_order_id',
        'order_number',
        'total_price',
        'currency',
        'financial_status',
        'fulfillment_status',
        'shopify_created_at',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'shopify_created_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the owner (User) that owns the order.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
