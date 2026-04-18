<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class InvoicePaymentService
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * Handle the checkout session completed event.
     */
    public function handleCheckoutSessionCompleted($session)
    {
        Log::info('Stripe Checkout Session Completed', ['session_id' => $session->id]);

        // Check if this is a registration payment (we pass email in metadata)
        $email = $session->metadata->email ?? null;
        $isRegistration = $session->metadata->is_registration ?? false;

        if ($email && $isRegistration === 'true') {
            Log::info("Processing registration payment for email: {$email}");
            $user = $this->registrationService->finalizeFromCache($email);

            if ($user) {
                // Record Payment
                Payment::create([
                    'user_id' => $user->id,
                    'pricing_plan_id' => $user->subscription->pricing_plan_id ?? null,
                    'external_payment_id' => $session->id,
                    'amount' => $session->amount_total / 100, // Stripe uses cents
                    'currency' => strtoupper($session->currency),
                    'status' => 'completed',
                    'payment_method' => 'card',
                ]);

                Log::info("User registration and payment recorded for: {$user->email}");
            } else {
                Log::error("Failed to complete registration finalize for: {$email}");
            }

        } else {
            // Handle regular invoice payments here...
            Log::info('Regular payment detected for: '.($email ?? 'unknown'));
        }
    }
}
