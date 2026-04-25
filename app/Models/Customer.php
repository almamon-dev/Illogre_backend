<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'shopify_customer_id',
        'name',
        'email',
        'phone',
        'country',
        'notes',
        'total_spent',
        'total_orders',
        'status',
        'last_interaction_at',
    ];

    /**
     * Relationship with the owner (company).
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relationship with tickets.
     * Assuming tickets will be linked to customers via email or a new customer_id column.
     * Let's check if we should add customer_id to tickets table.
     */
    public function tickets()
    {
        // For now, let's assume we link by email within the same owner_id
        return $this->hasMany(Ticket::class, 'customer_email', 'email');
    }
}
