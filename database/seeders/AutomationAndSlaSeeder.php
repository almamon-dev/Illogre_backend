<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\SlaPolicy;
use App\Models\User;
use Illuminate\Database\Seeder;

class AutomationAndSlaSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'mamon193p@gmail.com')->first();

        if (!$owner) {
            $this->command->warn('mamon193p@gmail.com not found. Skipping AutomationAndSlaSeeder.');
            return;
        }

        // 1. Create default SLA Policies
        SlaPolicy::create([
            'user_id' => $owner->id,
            'name' => 'High Priority SLA (1 Hour Reply)',
            'first_response_time_minutes' => 60, // 1 hour
            'resolution_time_minutes' => 1440, // 24 hours
            'is_active' => true,
        ]);

        SlaPolicy::create([
            'user_id' => $owner->id,
            'name' => 'Standard SLA (24 Hours Reply)',
            'first_response_time_minutes' => 1440, // 24 hours
            'resolution_time_minutes' => 4320, // 3 days
            'is_active' => true,
        ]);

        // 2. Create default Automation Rules
        AutomationRule::create([
            'user_id' => $owner->id,
            'name' => 'Assign Billing Issues to Finance',
            'conditions' => [
                [
                    'field' => 'category',
                    'operator' => 'equals',
                    'value' => 'Billing'
                ]
            ],
            'actions' => [
                [
                    'action' => 'set_priority',
                    'value' => 'High'
                ],
                [
                    'action' => 'add_tag',
                    'value' => 'finance-review'
                ]
            ],
            'is_active' => true,
        ]);

        AutomationRule::create([
            'user_id' => $owner->id,
            'name' => 'Auto-reply to Refund Requests',
            'conditions' => [
                [
                    'field' => 'subject',
                    'operator' => 'contains',
                    'value' => 'refund'
                ]
            ],
            'actions' => [
                [
                    'action' => 'send_auto_reply',
                    'value' => 'We have received your refund request. Please allow us 24-48 hours to process it.'
                ],
                [
                    'action' => 'set_status',
                    'value' => 'Pending'
                ]
            ],
            'is_active' => true,
        ]);

        $this->command->info('Default Automations and SLAs created for mamon193p@gmail.com');
    }
}
