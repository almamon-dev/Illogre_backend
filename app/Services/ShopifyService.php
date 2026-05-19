<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Exception;

class ShopifyService
{
    /**
     * Get customers from Shopify.
     */
    public function getCustomers(Integration $integration)
    {
        $shop = $integration->settings['shop_domain'] ?? $integration->provider_id;
        $accessToken = $integration->access_token;

        if (!$shop || !$accessToken) {
            throw new Exception("Shopify credentials missing for this integration.");
        }

        // Shopify Admin API URL
        $url = "https://{$shop}/admin/api/2024-04/customers.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get($url);

        if ($response->failed()) {
            throw new Exception("Shopify API error: " . $response->body());
        }

        return $response->json()['customers'] ?? [];
    }

    /**
     * Get orders from Shopify.
     */
    public function getOrders(Integration $integration)
    {
        $shop = $integration->settings['shop_domain'] ?? $integration->provider_id;
        $accessToken = $integration->access_token;

        if (!$shop || !$accessToken) {
            throw new Exception("Shopify credentials missing for this integration.");
        }

        // Shopify Admin API URL
        $url = "https://{$shop}/admin/api/2024-04/orders.json?status=any";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get($url);

        if ($response->failed()) {
            throw new Exception("Shopify API error: " . $response->body());
        }

        return $response->json()['orders'] ?? [];
    }
}
