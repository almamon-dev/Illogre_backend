<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_name',
        'customer_avatar',
        'subject',
        'category',
        'source',
        'confidence',
        'status',
        'assigned',
        'owner_id'
    ];
}
