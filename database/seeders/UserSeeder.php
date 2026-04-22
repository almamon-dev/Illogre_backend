<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Supe Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'user_type' => 'super_admin',
            'company_name' => 'Softvence',
            'email_verified_at' => now(),
            'status' => 'active',
            'terms_accepted_at' => now(),
        ]);

        // Create a Test Owner User
        $owner = User::create([
            'name' => 'John Owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'owner',
            'company_name' => 'Owner Corp Ltd',
            'email_verified_at' => now(),
            'status' => 'active',
            'terms_accepted_at' => now(),
        ]);

        // Create Subscription for Owner
        $plan = PricingPlan::where('name', 'Starter')->first();
        if ($plan) {
            UserSubscription::create([
                'user_id' => $owner->id,
                'pricing_plan_id' => $plan->id,
                'started_at' => now(),
                'expires_at' => now()->addMonth(),
                'status' => 'active',
                'is_trial' => false,
            ]);

            // Create Demo Payment for testing
            Payment::create([
                'user_id' => $owner->id,
                'pricing_plan_id' => $plan->id,
                'external_payment_id' => 'ch_test_' . str()->random(10),
                'amount' => $plan->price,
                'currency' => 'USD',
                'status' => 'completed',
                'payment_method' => 'card',
            ]);
        }

        // --- Create Support Manager (Parent is Owner) ---
        $manager = User::create([
            'name' => 'Mila Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'member',
            'role' => 'Support Manager',
            'parent_id' => $owner->id,
            'company_name' => $owner->company_name,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // --- Create Support Agent (Parent is Manager) ---
        User::create([
            'name' => 'Sarah Agent',
            'email' => 'agent@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'member',
            'role' => 'Support Agent',
            'parent_id' => $manager->id,
            'company_name' => $owner->company_name,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);


    }
}
