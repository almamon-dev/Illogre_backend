<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing plans
        Schema::disableForeignKeyConstraints();
        PricingPlan::truncate();
        Schema::enableForeignKeyConstraints();

        // Customer Plans from the screenshot
        $plans = [
            [
                'name' => 'Starter',
                'price' => 29.00,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 1,
                'features' => [
                    'Up to 500 tickets/month',
                    '2 team members',
                    'Gmail integration',
                    'Basic AI analysis',
                    'Email support',
                ],
            ],
            [
                'name' => 'Growth',
                'price' => 79.00,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => true,
                'order' => 2,
                'features' => [
                    'Up to 2,500 tickets/month',
                    '10 team members',
                    'All integrations',
                    'Advanced AI + automation',
                    'Priority support',
                    'Analytics dashboard',
                ],
            ],
            [
                'name' => 'Scale',
                'price' => 199.00,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 3,
                'features' => [
                    'Unlimited tickets',
                    'Unlimited team members',
                    'All integrations + API',
                    'Custom AI training',
                    'Advanced analytics',
                    'Dedicated support',
                    'SLA guarantee',
                ],
            ],
            
            // Annual versions (usually 10x or 20% off)
            [
                'name' => 'Starter (Annual)',
                'price' => 290.00, // 2 months free
                'billing_period' => 'annual',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 4,
                'features' => [
                    'Up to 500 tickets/month',
                    '2 team members',
                    'Gmail integration',
                    'Basic AI analysis',
                    'Email support',
                    '2 months free',
                ],
            ],
            [
                'name' => 'Growth (Annual)',
                'price' => 790.00,
                'billing_period' => 'annual',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => true,
                'order' => 5,
                'features' => [
                    'Up to 2,500 tickets/month',
                    '10 team members',
                    'All integrations',
                    'Advanced AI + automation',
                    'Priority support',
                    'Analytics dashboard',
                    '2 months free',
                ],
            ],
            [
                'name' => 'Scale (Annual)',
                'price' => 1990.00,
                'billing_period' => 'annual',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 6,
                'features' => [
                    'Unlimited tickets',
                    'Unlimited team members',
                    'All integrations + API',
                    'Custom AI training',
                    'Advanced analytics',
                    'Dedicated support',
                    'SLA guarantee',
                    '2 months free',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}
