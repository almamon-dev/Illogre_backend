<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Services\ShopifyService;

class TestIntegrationController extends Controller
{
    public function index()
    {
        $integrations = Integration::where('user_id', auth()->id())->get();
        
        return Inertia::render('Owner/Integrations/Test', [
            'integrations' => $integrations
        ]);
    }

    public function listCustomers(ShopifyService $shopifyService)
    {
        $integration = Integration::where('user_id', auth()->id())
                                  ->where('provider', 'shopify')
                                  ->first();

        if (!$integration) {
            return response()->json(['error' => 'Shopify not connected. Please connect from the test page first.'], 404);
        }

        try {
            $customers = $shopifyService->getCustomers($integration);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customers retrieved successfully',
                'metadata' => [
                    'shop' => $integration->provider_id,
                    'count' => count($customers),
                    'provider' => 'shopify',
                    'synced_at' => now()->toDateTimeString(),
                ],
                'data' => $customers
            ], 200, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shopify API error: ' . $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
