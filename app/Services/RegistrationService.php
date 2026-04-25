<?php

namespace App\Services;

use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class RegistrationService
{
    /**
     * Finalize registration from cache after payment success.
     */
    public function finalizeFromCache($cacheKey)
    {
        $userData = Cache::get("reg_{$cacheKey}");

        if (! $userData) {
            Log::error("Registration finalization failed: No cached data found for key {$cacheKey}");

            return null;
        }

        return DB::transaction(function () use ($userData, $cacheKey) {
            // 1. Update or Create User
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'], // Already hashed in Step 1
                    'user_type' => $userData['user_type'],
                    'role' => $userData['role'] ?? null,
                    'company_name' => $userData['company_name'] ?? null,
                    'terms_accepted_at' => $userData['terms_accepted_at'] ?? null,
                    'email_verified_at' => now(),
                    'status' => 'pending',
                    'payment_method' => 'card',
                ]
            );

            // 2. Cleanup
            Cache::forget("reg_{$cacheKey}");

            return $user;
        });
    }

    /**
     * Create a Stripe Checkout Session for registration.
     */
    public function createCheckoutSession(string $email, PricingPlan $plan)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        return $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower(config('services.stripe.currency', 'usd')),
                    'product_data' => [
                        'name' => $plan->name.' Plan Registration',
                    ],
                    'unit_amount' => $plan->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $email,
            'success_url' => config('services.stripe.success_url').'?email='.urlencode($email),
            'cancel_url' => config('services.stripe.cancel_url'),
            'metadata' => [
                'email' => $email,
                'is_registration' => 'true',
            ],
        ]);
    }
}
