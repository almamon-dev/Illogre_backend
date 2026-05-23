<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Integration;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    /**
     * Handle Shopify Customer Webhooks (Create/Update)
     */
    public function handleCustomers(Request $request)
    {
        $shopDomain = $request->header('x-shopify-shop-domain');
        Log::info("Shopify Webhook Customer received for: {$shopDomain}");
        
        $integration = $this->getIntegration($shopDomain);

        if (!$integration) {
            Log::error("Shopify Webhook Customer Error: Integration not found for shop domain: {$shopDomain}");
            return response()->json(['error' => 'Shop not found'], 404);
        }

        if (!$this->verifyWebhook($request, $integration)) {
            Log::error("Shopify Webhook Customer Error: HMAC verification failed for shop domain: {$shopDomain}");
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        Log::info("Shopify Webhook (Customer): " . $request->header('x-shopify-topic') . " for {$shopDomain}");

        if ($payload) {
            Customer::updateOrCreate(
                [
                    'shopify_customer_id' => $payload['id'],
                    'owner_id' => $integration->user_id
                ],
                [
                    'name' => trim(($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? '')),
                    'email' => $payload['email'] ?? null,
                    'phone' => $payload['phone'] ?? null,
                    'country' => $payload['default_address']['country'] ?? null,
                    'total_spent' => $payload['total_spent'] ?? 0,
                    'total_orders' => $payload['orders_count'] ?? 0,
                    'status' => $this->calculateStatus($payload['orders_count'] ?? 0, $payload['total_spent'] ?? 0),
                ]
            );
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle Shopify Order Webhooks (Create/Update)
     */
    public function handleOrders(Request $request)
    {
        $shopDomain = $request->header('x-shopify-shop-domain');
        Log::info("Shopify Webhook Order received for: {$shopDomain}");
        
        $integration = $this->getIntegration($shopDomain);

        if (!$integration) {
            Log::error("Shopify Webhook Order Error: Integration not found for shop domain: {$shopDomain}");
            return response()->json(['error' => 'Shop not found'], 404);
        }

        if (!$this->verifyWebhook($request, $integration)) {
            Log::error("Shopify Webhook Order Error: HMAC verification failed for shop domain: {$shopDomain}");
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        Log::info("Shopify Webhook (Order): " . $request->header('x-shopify-topic') . " for {$shopDomain}");

        if ($payload) {
            // First, find or create the customer for this order
            $customer = null;
            if (isset($payload['customer'])) {
                $customer = Customer::updateOrCreate(
                    [
                        'shopify_customer_id' => $payload['customer']['id'],
                        'owner_id' => $integration->user_id
                    ],
                    [
                        'name' => trim(($payload['customer']['first_name'] ?? '') . ' ' . ($payload['customer']['last_name'] ?? '')),
                        'email' => $payload['customer']['email'] ?? null,
                        'phone' => $payload['customer']['phone'] ?? null,
                    ]
                );
            }

            // Then, create or update the order
            Order::updateOrCreate(
                [
                    'shopify_order_id' => $payload['id'],
                    'owner_id' => $integration->user_id
                ],
                [
                    'customer_id' => $customer ? $customer->id : null,
                    'order_number' => $payload['name'] ?? $payload['order_number'] ?? null,
                    'total_price' => $payload['total_price'] ?? 0,
                    'currency' => $payload['currency'] ?? 'USD',
                    'financial_status' => $payload['financial_status'] ?? null,
                    'fulfillment_status' => $payload['fulfillment_status'] ?? null,
                    'shopify_created_at' => $payload['created_at'] ?? null,
                    'raw_data' => $payload,
                ]
            );
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Get integration for the shop domain
     */
    protected function getIntegration($shopDomain)
    {
        return Integration::where('provider', 'shopify')
            ->where('provider_id', $shopDomain)
            ->first();
    }

    /**
     * Verify Webhook Signature
     */
    protected function verifyWebhook(Request $request, $integration)
    {
        $hmac = $request->header('x-shopify-hmac-sha256');
        $data = $request->getContent();
        
        // Use user-specific secret if available, otherwise fallback to global
        $apiSecret = $integration->settings['api_secret'] ?? config('shopify.api_secret');

        if (!$apiSecret) {
            Log::error("Shopify API Secret missing for HMAC verification.");
            return false;
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $apiSecret, true));
        
        return hash_equals($hmac, $calculatedHmac);
    }

    /**
     * Simple status calculation logic.
     */
    protected function calculateStatus($orders, $spent)
    {
        if ($spent >= 500) return 'VIP';
        if ($orders >= 2) return 'Returning';
        return 'New';
    }
}
