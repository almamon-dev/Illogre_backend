<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Shopify\Auth\OAuth;

class ShopifyController extends Controller
{
    /**
     * Start the installation process
     */
    public function install(Request $request)
    {
        $shop = $request->query('shop');
        $userId = $request->query('user_id');

        if (! $shop) {
            return response()->json(['error' => 'Shop parameter is missing'], 400);
        }

        // We pass the user_id in the state to retrieve it in the callback
        return redirect(OAuth::begin(
            $shop,
            '/shopify/callback',
            false, // offline access
            $userId // Custom state (user_id)
        ));
    }

    /**
     * Handle the callback from Shopify
     */
    public function callback(Request $request)
    {
        try {
            $session = OAuth::callback(
                $request->cookie(),
                $request->query()
            );

            $shopDomain = $session->getShop();
            $accessToken = $session->getAccessToken();
            $userId = $request->query('state'); // Retrieve user_id from state

            // Link to User (Business Owner)
            $user = Auth::user() ?: \App\Models\User::find($userId);

            if ($user) {
                Integration::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'provider' => 'shopify',
                    ],
                    [
                        'access_token' => $accessToken,
                        'status' => 'connected',
                        'settings' => ['shop_domain' => $shopDomain],
                        'last_synced_at' => now(),
                    ]
                );

                // Redirect to the frontend integrations page
                return redirect()->away(config('app.frontend_url', env('APP_URL')).'/integrations?success=shopify');
            }

            return redirect('/admin/dashboard')->with('success', 'Shopify Store Connected Successfully!');

        } catch (\Exception $e) {
            Log::error('Shopify Auth Error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to connect Shopify: '.$e->getMessage()], 500);
        }
    }
}
