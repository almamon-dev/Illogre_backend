<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'sender_name',
        'body',
        'is_ai',
        'is_internal'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
