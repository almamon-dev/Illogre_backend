<?php

namespace Database\Seeders;


use App\Models\PricingPlan;
use App\Models\User;
use App\Models\UserSetting;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Admin User
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

        // 2. Create a Subscribed Owner
        $subscribedOwner = User::create([
            'name' => 'Subscribed Owner',
            'email' => 'mamon193p@gmail.com',
            'password' => Hash::make('password123'),
            'user_type' => 'owner',
            'role' => 'owner',
            'company_name' => 'Premium Corp',
            'email_verified_at' => now(),
            'status' => 'active',
            'terms_accepted_at' => now(),
        ]);

        // Seed default support email setting
        UserSetting::create([
            'user_id' => $subscribedOwner->id,
            'key' => 'support_email',
            'value' => 'mamon193p@gmail.com',
        ]);

        // Create Cashier subscription so $user->subscribed('default') returns true
        $plan = PricingPlan::where('name', 'Starter')->first();
        if ($plan) {
            // Cashier subscription — makes isSubscribed() return true
            $subscribedOwner->subscriptions()->create([
                'type' => 'default',
                'stripe_id' => 'sub_fake_' . uniqid(),
                'stripe_status' => 'active',
                'stripe_price' => 'price_fake_123',
                'quantity' => 1,
            ]);

        }

        // 3. Create an Unsubscribed Owner
        User::create([
            'name' => 'Unsubscribed Owner',
            'email' => 'free@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'owner',
            'role' => 'owner',
            'company_name' => 'Free Corp',
            'email_verified_at' => now(),
            'status' => 'active',
            'terms_accepted_at' => now(),
        ]);

        // 4. Create Support Manager (under Subscribed Owner)
        $manager = User::create([
            'name' => 'Manager Mila',
            'email' => 'manager@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'member',
            'role' => 'Support Manager',
            'parent_id' => $subscribedOwner->id,
            'company_name' => $subscribedOwner->company_name,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // 5. Create Support Agent (under Subscribed Owner directly)
        User::create([
            'name' => 'Agent Alex',
            'email' => 'agent@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'member',
            'role' => 'Support Agent',
            'parent_id' => $subscribedOwner->id,
            'company_name' => $subscribedOwner->company_name,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // 6. Create Support Agent (under Manager)
        User::create([
            'name' => 'Manager\'s Agent',
            'email' => 'managed_agent@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'member',
            'role' => 'Support Agent',
            'parent_id' => $manager->id,
            'company_name' => $subscribedOwner->company_name,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
    }
}
