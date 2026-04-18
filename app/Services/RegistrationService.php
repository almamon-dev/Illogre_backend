<?php

namespace App\Services;

use App\Models\PricingPlan;
use App\Models\User;
use App\Models\UserSubscription;
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
            // 1. Create User
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'], // Already hashed in Step 1
                'user_type' => $userData['user_type'],
                'company_name' => $userData['company_name'] ?? null,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);

            // 2. Create Subscription
            $plan = PricingPlan::find($userData['pricing_plan_id']);
            if ($plan) {
                $expiresAt = now();
                if ($plan->billing_period === 'trial') {
                    $expiresAt = now()->addDays($plan->trial_days);
                } elseif ($plan->billing_period === 'monthly') {
                    $expiresAt = now()->addMonth();
                } elseif ($plan->billing_period === 'quarterly') {
                    $expiresAt = now()->addMonths(3);
                } elseif ($plan->billing_period === 'annual') {
                    $expiresAt = now()->addYear();
                }

                UserSubscription::create([
                    'user_id' => $user->id,
                    'pricing_plan_id' => $plan->id,
                    'started_at' => now(),
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                    'is_trial' => $plan->billing_period === 'trial',
                ]);
            }

            // 3. Cleanup
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
                        'name' => $plan->name . ' Plan Registration',
                    ],
                    'unit_amount' => $plan->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $email,
            'success_url' => url('/registration/success?email=' . urlencode($email)),
            'cancel_url' => url('/registration/cancel'),
            'metadata' => [
                'email' => $email,
                'is_registration' => 'true'
            ]
        ]);
    }


}
