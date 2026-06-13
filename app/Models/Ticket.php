<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_name',
        'customer_email',
        'customer_avatar',
        'subject',
        'category',
        'source',
        'confidence',
        'status',
        'priority',
        'assigned',
        'customer_id',
        'owner_id',
        'body',
        'ai_suggested_reply',
        'ai_analysis'
    ];

    protected $casts = [
        'ai_analysis' => 'array',
    ];

    /**
     * Get the customer associated with the ticket.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the owner (User) that owns the ticket.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
