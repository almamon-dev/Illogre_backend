<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\BillingOverviewResource;
use App\Services\Owner\BillingService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class BillingController extends Controller
{
    use ApiResponse;

    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->billingService->getBillingData();
            
            return $this->sendResponse(
                new BillingOverviewResource($data), 
                'Billing and subscription data fetched successfully.'
            );
            
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch billing data.', [$e->getMessage()], 500);
        }
    }

    /**
     * Create a new Checkout Session for Subscription
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate(['pricing_plan_id' => 'required|exists:pricing_plans,id']);

        try {
            $user = $request->user();
            $plan = \App\Models\PricingPlan::findOrFail($request->pricing_plan_id);

            if (!$plan->stripe_price_id) {
                return $this->sendError('Selected plan is not synced with Stripe correctly.', [], 400);
            }

            // Create Cashier Checkout Session
            $checkout = $user->newSubscription('default', $plan->stripe_price_id)
                ->checkout([
                    'success_url' => env('FRONTEND_URL') . '/owner/billing?success=true',
                    'cancel_url' => env('FRONTEND_URL') . '/owner/billing?canceled=true',
                ]);

            return $this->sendResponse(['url' => $checkout->url], 'Checkout URL created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create checkout session.', [$e->getMessage()], 500);
        }
    }

    /**
     * Get Customer Portal URL (for managing everything)
     */
    public function portal(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->hasStripeId()) {
                return $this->sendError('No billing history found for this user.', [], 404);
            }

            $url = $user->billingPortalUrl(env('FRONTEND_URL') . '/owner/billing');

            return $this->sendResponse(['url' => $url], 'Billing portal URL generated.');
        } catch (Exception $e) {
            return $this->sendError('Failed to generate billing portal URL.', [$e->getMessage()], 500);
        }
    }

    /**
     * Cancel Auto-Renewal
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->subscribed('default')) {
                return $this->sendError('You do not have an active subscription.', [], 400);
            }

            $user->subscription('default')->cancel();

            return $this->sendResponse([], 'Subscription auto-renewal has been cancelled.');
        } catch (Exception $e) {
            return $this->sendError('Failed to cancel subscription.', [$e->getMessage()], 500);
        }
    }

    /**
     * Resume Auto-Renewal
     */
    public function resume(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->subscribed('default') || !$user->subscription('default')->onGracePeriod()) {
                return $this->sendError('You do not have a canceled subscription to resume.', [], 400);
            }

            $user->subscription('default')->resume();

            return $this->sendResponse([], 'Subscription auto-renewal has been resumed.');
        } catch (Exception $e) {
            return $this->sendError('Failed to resume subscription.', [$e->getMessage()], 500);
        }
    }

    /**
     * Swap / Update Package
     */
    public function swap(Request $request): JsonResponse
    {
        $request->validate(['pricing_plan_id' => 'required|exists:pricing_plans,id']);

        try {
            $user = $request->user();
            $plan = \App\Models\PricingPlan::findOrFail($request->pricing_plan_id);

            if (!$user->subscribed('default')) {
                return $this->sendError('You do not have an active subscription to update. Please create a new subscription instead.', [], 400);
            }

            if (!$plan->stripe_price_id) {
                return $this->sendError('Selected plan is not synced with Stripe correctly.', [], 400);
            }

            $user->subscription('default')->swap($plan->stripe_price_id);

            return $this->sendResponse([], 'Package updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to update package.', [$e->getMessage()], 500);
        }
    }
}
