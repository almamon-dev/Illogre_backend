<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegrationApiController extends Controller
{
    use ApiResponse;

    /**
     * List all integrations with their current status for the user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $userIntegrations = $user->integrations->keyBy('provider');

        $availableProviders = [
            [
                'id' => 'shopify',
                'name' => 'Shopify',
                'description' => 'Sync orders, customers, and products',
                'icon' => 'shopify', // Use icon names that the frontend can map
                'category' => 'E-commerce',
            ],
            [
                'id' => 'gmail',
                'name' => 'Gmail',
                'description' => 'Convert support emails into tickets instantly.',
                'icon' => 'gmail',
                'category' => 'Email',
            ],
            [
                'id' => 'outlook',
                'name' => 'Outlook',
                'description' => 'Sync orders, customers, and products',
                'icon' => 'outlook',
                'category' => 'Email',
            ],
            [
                'id' => 'whatsapp',
                'name' => 'WhatsApp Business',
                'description' => 'Manage customer conversations via WhatsApp',
                'icon' => 'whatsapp',
                'category' => 'Messaging',
            ],
            [
                'id' => 'slack',
                'name' => 'Slack',
                'description' => 'Sync orders, customers, and products',
                'icon' => 'slack',
                'category' => 'Collaboration',
            ],
            [
                'id' => 'instagram',
                'name' => 'Instagram DM',
                'description' => 'Respond to Instagram messages from Tixolve',
                'icon' => 'instagram',
                'category' => 'Social',
            ],
            [
                'id' => 'klaviyo',
                'name' => 'Klaviyo',
                'description' => 'Sync customer data with marketing campaigns',
                'icon' => 'klaviyo',
                'category' => 'Marketing',
            ],
            [
                'id' => 'zendesk',
                'name' => 'Zendesk',
                'description' => 'Import tickets, contacts, and knowledge base',
                'icon' => 'zendesk',
                'category' => 'Migration',
                'type' => 'migrate',
            ],
            [
                'id' => 'gorgias',
                'name' => 'Gorgias',
                'description' => 'Import tickets and customer conversations',
                'icon' => 'gorgias',
                'category' => 'Migration',
                'type' => 'migrate',
            ],
            [
                'id' => 'zoho',
                'name' => 'Zoho Desk',
                'description' => 'Import tickets and customer support data',
                'icon' => 'zoho',
                'category' => 'Migration',
                'type' => 'migrate',
            ],
        ];

        $data = collect($availableProviders)->map(function ($provider) use ($userIntegrations) {
            $integration = $userIntegrations->get($provider['id']);

            return array_merge($provider, [
                'status' => $integration ? $integration->status : 'available',
                'is_connected' => $integration ? $integration->status === 'connected' : false,
                'last_synced' => $integration ? $integration->last_synced_at?->diffForHumans() : null,
                'integration_id' => $integration ? $integration->id : null,
            ]);
        });

        return $this->sendResponse($data, 'Integrations retrieved successfully.');
    }

    /**
     * Connect to a provider (Initial step).
     * In a real app, this would return an OAuth URL.
     */
    /**
     * Connect to a provider (OAuth Flow).
     * Returns a signed URL to start the OAuth process securely.
     */
    public function connect(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();

        // Specific logic for Shopify (Direct Credential/Private App Model)
        if ($provider === 'shopify') {
            $request->validate([
                'shop' => 'required|string',
                'api_key' => 'required|string',
                'access_token' => 'required|string',
            ]);

            $integration = Integration::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'provider' => 'shopify',
                ],
                [
                    'provider_id' => $request->shop,
                    'access_token' => $request->access_token,
                    'status' => 'connected',
                    'settings' => [
                        'shop_domain' => $request->shop,
                        'api_key' => $request->api_key,
                    ],
                    'last_synced_at' => now(),
                ]
            );

            return $this->sendResponse($integration, 'Shopify integration connected successfully.');
        }

        return $this->sendError('Provider not supported for direct connection yet.', [], 400);
    }

    /**
     * Disconnect an integration.
     */
    public function disconnect(Request $request, $id): JsonResponse
    {
        $integration = $request->user()->integrations()->findOrFail($id);
        $integration->delete();

        return $this->sendResponse([], 'Integration disconnected successfully.');
    }

    /**
     * Redirect to Shopify for OAuth authorization using owner's own credentials
     */
    public function shopifyInstall(Request $request)
    {
        $request->validate([
            'shop' => 'required|string',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $shop = $request->shop;
        $apiKey = $request->api_key;
        $apiSecret = $request->api_secret;
        
        // Temporarily store credentials in session or database to use in callback
        // We'll save them to the integration record now
        Integration::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'provider' => 'shopify',
            ],
            [
                'provider_id' => $shop,
                'status' => 'available', // Not connected yet
                'settings' => [
                    'shop_domain' => $shop,
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
                ],
            ]
        );

        $scopes = "read_products,read_orders,read_customers,write_products,write_orders,write_customers";
        $redirectUri = route('owner.shopify.callback');

        // Build the Shopify authorization URL using the OWNER'S API Key
        $installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}";

        return redirect()->away($installUrl);
    }

    /**
     * Handle the callback from Shopify OAuth
     */
    public function shopifyCallback(Request $request)
    {
        $shop = $request->shop;
        $code = $request->code;

        if (!$shop || !$code) {
            return redirect()->route('owner.test-shopify')->with('error', 'Invalid request from Shopify.');
        }

        // Retrieve the owner's credentials from the database
        $integration = Integration::where('provider_id', $shop)
                                  ->where('provider', 'shopify')
                                  ->first();

        if (!$integration || !isset($integration->settings['api_key'])) {
            return redirect()->route('owner.test-shopify')->with('error', 'Credentials not found in database.');
        }

        $apiKey = $integration->settings['api_key'];
        $apiSecret = $integration->settings['api_secret'];

        // Exchange the authorization code for an access token using OWNER'S secret
        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => $apiKey,
            'client_secret' => $apiSecret,
            'code' => $code,
        ]);

        if ($response->failed()) {
            return redirect()->route('owner.test-shopify')->with('error', 'Failed to exchange token: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // Update the integration with the permanent access token
        $integration->update([
            'access_token' => $accessToken,
            'status' => 'connected',
            'settings' => array_merge($integration->settings, [
                'scopes' => $data['scope'] ?? null,
            ]),
            'last_synced_at' => now(),
        ]);

        // Automatically register webhooks for this shop
        $this->registerWebhooks($integration);

        return redirect()->route('owner.test-shopify')->with('success', 'Shopify connected successfully!');
    }

    /**
     * Register necessary webhooks with Shopify
     */
    private function registerWebhooks($integration)
    {
        $shop = $integration->settings['shop_domain'];
        $accessToken = $integration->access_token;
        
        $topics = [
            'customers/create' => route('api.webhooks.shopify.customers'),
            'customers/update' => route('api.webhooks.shopify.customers'),
            'orders/create' => route('api.webhooks.shopify.orders'),
            'orders/updated' => route('api.webhooks.shopify.orders'),
        ];

        foreach ($topics as $topic => $address) {
            // Replace localhost with a real URL if needed (Shopify doesn't like localhost)
            // But for development, the user might be using ngrok.
            // We'll assume the 'route' function returns the correct accessible URL.
            
            Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->post("https://{$shop}/admin/api/2024-04/webhooks.json", [
                'webhook' => [
                    'topic' => $topic,
                    'address' => $address,
                    'format' => 'json',
                ],
            ]);
        }
    }
}
