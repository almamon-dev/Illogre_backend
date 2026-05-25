<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;
use Laravel\Cashier\Cashier;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stripe = Cashier::stripe();

        // 1. Fetch all active products and prices from Stripe to prevent duplicates
        $stripePrices = $stripe->prices->all(['active' => true, 'limit' => 100, 'expand' => ['data.product']]);
        
        $existingStripePlans = [];
        foreach ($stripePrices->data as $price) {
            if ($price->product->active) {
                // Key by product name + billing interval
                $interval = $price->recurring->interval ?? 'month';
                $key = strtolower($price->product->name . '-' . $interval);
                $existingStripePlans[$key] = $price;
            }
        }

        // 2. Define the default plans we want to seed
        $defaultPlans = [
            // Starter - Monthly
            [
                'name' => 'Starter',
                'price' => 29.00,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 1,
                'features' => ['Up to 500 tickets/month', '2 team members', 'Gmail integration', 'Basic AI analysis', 'Email support'],
                'limits' => ['ticket_limit' => 500, 'member_limit' => 2, 'ai_limit' => 1000]
            ],
            // Starter - Annual
            [
                'name' => 'Starter',
                'price' => 290.00,
                'billing_period' => 'annual',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 2,
                'features' => ['Up to 500 tickets/month', '2 team members', 'Gmail integration', 'Basic AI analysis', 'Email support'],
                'limits' => ['ticket_limit' => 500, 'member_limit' => 2, 'ai_limit' => 1000]
            ],
            // Growth - Monthly
            [
                'name' => 'Growth',
                'price' => 79.00,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => true,
                'order' => 3,
                'features' => ['Up to 2,500 tickets/month', '10 team members', 'All integrations', 'Advanced AI + automation', 'Priority support', 'Analytics dashboard'],
                'limits' => ['ticket_limit' => 2500, 'member_limit' => 10, 'ai_limit' => 10000]
            ],
            // Growth - Annual
            [
                'name' => 'Growth',
                'price' => 790.00,
                'billing_period' => 'annual',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => true,
                'order' => 4,
                'features' => ['Up to 2,500 tickets/month', '10 team members', 'All integrations', 'Advanced AI + automation', 'Priority support', 'Analytics dashboard'],
                'limits' => ['ticket_limit' => 2500, 'member_limit' => 10, 'ai_limit' => 10000]
            ],
            // Scale - Monthly
            [
                'name' => 'Scale',
                'price' => 199.00,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 5,
                'features' => ['Unlimited tickets', 'Unlimited team members', 'All integrations + API', 'Custom AI training', 'Advanced analytics', 'Dedicated support', 'SLA guarantee'],
                'limits' => ['ticket_limit' => 999999, 'member_limit' => 999999, 'ai_limit' => 999999]
            ],
            // Scale - Annual
            [
                'name' => 'Scale',
                'price' => 1990.00,
                'billing_period' => 'annual',
                'trial_days' => 0,
                'is_active' => true,
                'is_popular' => false,
                'order' => 6,
                'features' => ['Unlimited tickets', 'Unlimited team members', 'All integrations + API', 'Custom AI training', 'Advanced analytics', 'Dedicated support', 'SLA guarantee'],
                'limits' => ['ticket_limit' => 999999, 'member_limit' => 999999, 'ai_limit' => 999999]
            ],
        ];

        foreach ($defaultPlans as $planData) {
            $interval = $planData['billing_period'] === 'annual' ? 'year' : 'month';
            $key = strtolower($planData['name'] . '-' . $interval);

            $productId = null;
            $priceId = null;

            // Check if it already exists in Stripe
            if (isset($existingStripePlans[$key])) {
                $stripePrice = $existingStripePlans[$key];
                $productId = $stripePrice->product->id;
                $priceId = $stripePrice->id;
            } else {
                // Create in Stripe
                $product = $stripe->products->create([
                    'name' => $planData['name'],
                    'active' => $planData['is_active'],
                ]);
                $productId = $product->id;

                $price = $stripe->prices->create([
                    'product' => $productId,
                    'unit_amount' => $planData['price'] * 100, // cents
                    'currency' => env('STRIPE_CURRENCY', 'usd'),
                    'recurring' => ['interval' => $interval],
                ]);
                $priceId = $price->id;
            }

            // Create locally
            $localPlan = PricingPlan::updateOrCreate(
                ['name' => $planData['name'], 'billing_period' => $planData['billing_period']],
                [
                    'stripe_product_id' => $productId,
                    'stripe_price_id' => $priceId,
                    'price' => $planData['price'],
                    'trial_days' => $planData['trial_days'],
                    'is_active' => $planData['is_active'],
                    'is_popular' => $planData['is_popular'],
                    'order' => $planData['order'],
                ]
            );

            // Sync Features
            $localPlan->planFeatures()->delete(); // Clear old ones

            foreach ($planData['features'] as $featureName) {
                $localPlan->planFeatures()->create([
                    'name' => $featureName,
                    'value' => null,
                    'is_limit' => false,
                ]);
            }

            foreach ($planData['limits'] as $limitName => $limitValue) {
                $localPlan->planFeatures()->create([
                    'name' => $limitName,
                    'value' => (string)$limitValue,
                    'is_limit' => true,
                ]);
            }
        }
    }
}
