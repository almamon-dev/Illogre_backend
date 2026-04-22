<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\UserSubscription;
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

            $payment = Payment::where('external_payment_id', $session->id)->first();

            if ($payment) {
                $payment->update(['status' => 'completed']);

                // Update subscription status
                $subscription = UserSubscription::where('user_id', $payment->user_id)->first();
                if ($subscription) {
                    $subscription->update(['status' => 'active']);
                }

                // Finalize from cache (cleanup)
                $this->registrationService->finalizeFromCache($email);

                Log::info("User registration and payment completed for: {$email}");
            } else {
                Log::error("Payment record not found for session: {$session->id}");
            }

        } else {
            // Handle regular invoice payments here...
            Log::info('Regular payment detected for: '.($email ?? 'unknown'));
        }
    }
}
