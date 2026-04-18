<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\InvoicePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    protected $paymentService;

    public function __construct(InvoicePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->paymentService->handleCheckoutSessionCompleted($session);
                break;
            // Handle other event types
            default:
                Log::info('Unhandled Stripe event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }
}
