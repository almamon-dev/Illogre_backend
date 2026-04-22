<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = \App\Models\User::where('email', 'owner@test.com')->first();

        if (!$owner) return;

        $tickets = [
            [
                'ticket_number' => 'ORD-10024',
                'customer_name' => 'Sarah',
                'subject' => 'Where is my order?',
                'category' => 'Order Status',
                'source' => 'Chat',
                'confidence' => 82,
                'status' => 'Resolved',
                'assigned' => 'AI Agent',
                'owner_id' => $owner->id,
            ],
            [
                'ticket_number' => 'ORD-10025',
                'customer_name' => 'John Doe',
                'subject' => 'Refund request for #445',
                'category' => 'Billing',
                'source' => 'Email',
                'confidence' => 15,
                'status' => 'Pending',
                'assigned' => 'Admin',
                'owner_id' => $owner->id,
            ],
            [
                'ticket_number' => 'ORD-10026',
                'customer_name' => 'Mila Kunis',
                'subject' => 'Account setup help',
                'category' => 'Support',
                'source' => 'Shopify',
                'confidence' => 95,
                'status' => 'Resolved',
                'assigned' => 'AI Agent',
                'owner_id' => $owner->id,
            ]
        ];

        foreach ($tickets as $ticket) {
            \App\Models\Ticket::create($ticket);
        }
    }
}
