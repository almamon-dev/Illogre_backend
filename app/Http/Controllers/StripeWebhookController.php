<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Models\PricingPlan;
use Illuminate\Support\Str;

class StripeWebhookController extends CashierController
{
    /**
     * Handle product created or updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleProductCreated(array $payload)
    {
        return $this->handleProductUpdated($payload);
    }

    protected function handleProductUpdated(array $payload)
    {
        $product = $payload['data']['object'];

        PricingPlan::updateOrCreate(
            ['stripe_product_id' => $product['id']],
            [
                'name' => $product['name'],
                'is_active' => $product['active'],
                // Add default values for required fields if a new plan is created
                'billing_period' => 'monthly', 
                'price' => 0, 
                'trial_days' => 0,
            ]
        );

        return $this->successMethod();
    }
    
    /**
     * Handle price created or updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handlePriceCreated(array $payload)
    {
        return $this->handlePriceUpdated($payload);
    }

    protected function handlePriceUpdated(array $payload)
    {
        $price = $payload['data']['object'];

        $plan = PricingPlan::where('stripe_product_id', $price['product'])->first();

        if ($plan) {
            $billingPeriod = 'monthly';
            if (isset($price['recurring']['interval'])) {
                $interval = $price['recurring']['interval'];
                if ($interval === 'month') $billingPeriod = 'monthly';
                if ($interval === 'year') $billingPeriod = 'annual';
                if ($interval === 'week') $billingPeriod = 'monthly'; // Custom map if needed
                if ($interval === 'day') $billingPeriod = 'monthly'; 
            }

            $plan->update([
                'stripe_price_id' => $price['id'],
                'price' => $price['unit_amount'] / 100, // Stripe stores in cents
                'billing_period' => $billingPeriod,
            ]);
        }

        return $this->successMethod();
    }
    
    /**
     * Handle product deleted.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleProductDeleted(array $payload)
    {
        $product = $payload['data']['object'];

        PricingPlan::where('stripe_product_id', $product['id'])->delete();

        return $this->successMethod();
    }
}
