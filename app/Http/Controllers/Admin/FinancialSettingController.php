<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use Inertia\Inertia;

class FinancialSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'stripe_key' => config('services.stripe.key'),
            'stripe_secret' => config('services.stripe.secret'),
            'stripe_webhook_secret' => config('services.stripe.webhook_secret'),
        ];

        return Inertia::render('Admin/Settings/Financial/Gateway', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
        ]);

        $envMapping = [
            'stripe_key' => 'STRIPE_KEY',
            'stripe_secret' => 'STRIPE_SECRET',
            'stripe_webhook_secret' => 'STRIPE_WEBHOOK_SECRET',
        ];

        foreach ($validated as $key => $value) {
            if (isset($envMapping[$key])) {
                $this->setEnv($envMapping[$key], $value);
            }
        }

        return back()->with('success', 'Financial settings updated successfully.');
    }

    protected function setEnv($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            // If the key exists, replace it, otherwise append it
            if (strpos($content, "{$key}=") !== false) {
                // Handle values with spaces or special characters by quoting them
                $escapedValue = '"' . addslashes($value) . '"';
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
            } else {
                $content .= "\n{$key}=\"" . addslashes($value) . "\"\n";
            }

            file_put_contents($path, $content);
        }
    }
}
