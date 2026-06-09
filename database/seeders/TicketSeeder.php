<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'mamon193p@gmail.com')->first();

        if (!$owner) {
            $this->command->warn('mamon193p@gmail.com not found. Skipping TicketSeeder.');
            return;
        }

        $tickets = [
            [
                'ticket_number'  => 'TKT-0001',
                'customer_name'  => 'Alice Johnson',
                'customer_email' => 'alice@example.com',
                'subject'        => 'Cannot login to my account',
                'body'           => 'I have been trying to login for the past hour but keep getting an error saying invalid credentials. I already reset my password twice but the problem persists.',
                'category'       => 'Account',
                'source'         => 'Email',
                'confidence'     => 92,
                'status'         => 'Open',
                'priority'       => 'High',
                'assigned'       => 'AI Agent',
            ],
            [
                'ticket_number'  => 'TKT-0002',
                'customer_name'  => 'Bob Martinez',
                'customer_email' => 'bob@example.com',
                'subject'        => 'Billing charge not recognized',
                'body'           => 'I noticed a charge on my credit card statement from your company that I do not recognize. Please help me understand what this is for.',
                'category'       => 'Billing',
                'source'         => 'Chat',
                'confidence'     => 87,
                'status'         => 'Open',
                'priority'       => 'High',
                'assigned'       => 'AI Agent',
            ],
            [
                'ticket_number'  => 'TKT-0003',
                'customer_name'  => 'Carol White',
                'customer_email' => 'carol@example.com',
                'subject'        => 'Feature request: Dark mode support',
                'body'           => 'It would be great if you could add a dark mode option to the dashboard. Many of us work late at night and bright screens are tough on the eyes.',
                'category'       => 'Feature Request',
                'source'         => 'Email',
                'confidence'     => 75,
                'status'         => 'Open',
                'priority'       => 'Low',
                'assigned'       => 'AI Agent',
            ],
            [
                'ticket_number'  => 'TKT-0004',
                'customer_name'  => 'David Lee',
                'customer_email' => 'david@example.com',
                'subject'        => 'App crashes when uploading large files',
                'body'           => 'Every time I try to upload a file larger than 10MB the application freezes and then crashes. This is happening on both Chrome and Firefox.',
                'category'       => 'Bug',
                'source'         => 'Chat',
                'confidence'     => 95,
                'status'         => 'Open',
                'priority'       => 'Medium',
                'assigned'       => 'AI Agent',
            ],
            [
                'ticket_number'  => 'TKT-0005',
                'customer_name'  => 'Emma Davis',
                'customer_email' => 'emma@example.com',
                'subject'        => 'How do I export my data?',
                'body'           => 'I need to export all my customer data to a CSV file for a report. I looked through the settings but could not find any export option. Is this feature available?',
                'category'       => 'General',
                'source'         => 'Email',
                'confidence'     => 80,
                'status'         => 'Open',
                'priority'       => 'Medium',
                'assigned'       => 'AI Agent',
            ],
        ];

        foreach ($tickets as $data) {
            Ticket::create(array_merge($data, [
                'owner_id' => $owner->id,
            ]));
        }

        $this->command->info('5 dummy tickets created for mamon193p@gmail.com');
    }
}
