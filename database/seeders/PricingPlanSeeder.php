<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use App\Models\PricingPlanFeature;
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
        PricingPlanFeature::truncate();
        Schema::enableForeignKeyConstraints();

        $plans = [
            [
                'name' => 'Starter',
                'price' => 29.00,
                'billing_period' => 'monthly',
                'order' => 1,
                'is_popular' => false,
                'limits' => [
                    'ticket_limit' => 500,
                    'member_limit' => 2,
                    'ai_limit' => 1000,
                ],
                'display_features' => [
                    'Up to 500 tickets/month',
                    '2 team members',
                    'Gmail integration',
                    'Basic AI analysis',
                    'Email support',
                ]
            ],
            [
                'name' => 'Growth',
                'price' => 79.00,
                'billing_period' => 'monthly',
                'order' => 2,
                'is_popular' => true,
                'limits' => [
                    'ticket_limit' => 2500,
                    'member_limit' => 10,
                    'ai_limit' => 10000,
                ],
                'display_features' => [
                    'Up to 2,500 tickets/month',
                    '10 team members',
                    'All integrations',
                    'Advanced AI + automation',
                    'Priority support',
                    'Analytics dashboard',
                ]
            ],
            [
                'name' => 'Scale',
                'price' => 199.00,
                'billing_period' => 'monthly',
                'order' => 3,
                'is_popular' => false,
                'limits' => [
                    'ticket_limit' => 999999, // Unlimited
                    'member_limit' => 999, // Unlimited
                    'ai_limit' => 999999,
                ],
                'display_features' => [
                    'Unlimited tickets',
                    'Unlimited team members',
                    'All integrations + API',
                    'Custom AI training',
                    'Advanced analytics',
                    'Dedicated support',
                    'SLA guarantee',
                ]
            ],
        ];

        foreach ($plans as $planData) {
            $plan = PricingPlan::create([
                'name' => $planData['name'],
                'price' => $planData['price'],
                'billing_period' => $planData['billing_period'],
                'order' => $planData['order'],
                'is_popular' => $planData['is_popular'],
                'features' => $planData['display_features']
            ]);

            // Add numeric limits to features table (for dashboard logic)
            foreach ($planData['limits'] as $name => $value) {
                PricingPlanFeature::create([
                    'pricing_plan_id' => $plan->id,
                    'name' => $name,
                    'value' => $value,
                    'is_limit' => true
                ]);
            }

            // Add all display features to table (for reference)
            foreach ($planData['display_features'] as $featureName) {
                PricingPlanFeature::create([
                    'pricing_plan_id' => $plan->id,
                    'name' => $featureName,
                    'value' => 'true',
                    'is_limit' => false
                ]);
            }
        }
    }
}
