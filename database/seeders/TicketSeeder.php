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

        $faker = \Faker\Factory::create();

        $categories = ['Account', 'Billing', 'Feature Request', 'Bug', 'General'];
        $sources = ['Email', 'Chat', 'API', 'Web'];
        $statuses = ['Open', 'Pending', 'Resolved', 'Closed'];
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];

        for ($i = 0; $i < 5; $i++) {
            $category = $faker->randomElement($categories);
            $subject = $faker->sentence(6);
            
            // Generate a fake AI response based on the category
            $aiReply = "Hello! I am the AI Assistant. Based on your inquiry regarding {$category}, here is a suggested resolution: Please let us know if you need further assistance with '{$subject}'. We are here to help!";

            Ticket::create([
                'ticket_number'  => 'TKT-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'customer_name'  => $faker->name(),
                'customer_email' => $faker->safeEmail(),
                'subject'        => $subject,
                'body'           => $faker->paragraph(4),
                'category'       => $category,
                'source'         => $faker->randomElement($sources),
                'confidence'     => $faker->numberBetween(50, 99),
                'status'         => $faker->randomElement($statuses),
                'priority'       => $faker->randomElement($priorities),
                'assigned'       => 'AI Agent',
                'owner_id'       => $owner->id,
                'ai_suggested_reply' => $aiReply,
            ]);
        }

        $this->command->info('5 dynamic tickets created for mamon193p@gmail.com');
    }
}
