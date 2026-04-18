<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Website System Settings
     */
    public function websiteSystem()
    {
        $keys = [
            'site_name',
            'site_url',
            'title_prefix',
            'meta_description',
            'keywords',
        ];

        $dbSettings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        $settings = [
            'site_name' => $dbSettings['site_name'] ?? config('app.name'),
            'site_url' => $dbSettings['site_url'] ?? config('app.url'),
            'title_prefix' => $dbSettings['title_prefix'] ?? '',
            'meta_description' => $dbSettings['meta_description'] ?? '',
            'keywords' => $dbSettings['keywords'] ?? '',
        ];

        return Inertia::render('Admin/Settings/Website/System', [
            'settings' => $settings,
        ]);

    }

    /**
     * Financial Gateway Settings
     */
    public function financialGateway()
    {
        $keys = [
            'stripe_mode',
            'stripe_key',
            'stripe_secret',
            'stripe_webhook_secret',
        ];

        $dbSettings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        $settings = [
            'stripe_mode' => $dbSettings['stripe_mode'] ?? 'test',
            'stripe_key' => $dbSettings['stripe_key'] ?? config('services.stripe.key'),
            'stripe_secret' => $dbSettings['stripe_secret'] ?? config('services.stripe.secret'),
            'stripe_webhook_secret' => $dbSettings['stripe_webhook_secret'] ?? config('services.stripe.webhook_secret'),
        ];

        return Inertia::render('Admin/Settings/Financial/Gateway', [
            'settings' => $settings,
        ]);
    }

    /**
     * Email Settings
     */
    public function emailSettings()
    {
        $settings = [
            'mail_mailer' => env('MAIL_MAILER', 'smtp'),
            'mail_host' => env('MAIL_HOST', '127.0.0.1'),
            'mail_port' => env('MAIL_PORT', 587),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => env('MAIL_PASSWORD', ''),
            'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'mail_from_name' => env('MAIL_FROM_NAME', 'Example'),
        ];

        return Inertia::render('Admin/Settings/System/Email', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update Email settings in .env
     */
    public function updateEmail(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // Map frontend keys to .env keys
        $envData = [
            'MAIL_MAILER' => $data['mail_mailer'] ?? 'smtp',
            'MAIL_HOST' => $data['mail_host'] ?? '',
            'MAIL_PORT' => $data['mail_port'] ?? '',
            'MAIL_USERNAME' => $data['mail_username'] ?? '',
            'MAIL_PASSWORD' => $data['mail_password'] ?? '',
            'MAIL_ENCRYPTION' => $data['mail_encryption'] ?? '',
            'MAIL_FROM_ADDRESS' => $data['mail_from_address'] ?? '',
            'MAIL_FROM_NAME' => $data['mail_from_name'] ?? '',
        ];

        $this->updateEnv($envData);

        return redirect()->back()->with('success', 'Email settings updated in .env successfully.');
    }

    /**
     * Private helper to update .env file
     */
    private function updateEnv(array $data)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            foreach ($data as $key => $value) {
                // If value has spaces or special chars, wrap in quotes
                $safeValue = (strpos($value, ' ') !== false || strpos($value, '€') !== false) ? "\"$value\"" : $value;

                if (preg_match("/^{$key}=/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$safeValue}", $content);
                } else {
                    $content .= "\n{$key}={$safeValue}";
                }
            }

            file_put_contents($path, $content);
        }
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $settings = $request->except(['_token', '_method']);

        // Keys that should also be updated in .env
        $envKeysMapping = [
            'stripe_key' => 'STRIPE_KEY',
            'stripe_secret' => 'STRIPE_SECRET',
            'stripe_webhook_secret' => 'STRIPE_WEBHOOK_SECRET',
        ];

        $envUpdateData = [];

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $finalValue = json_encode($value);
            } else {
                $finalValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $finalValue]
            );

            // Collect data for .env update if matches mapping
            if (isset($envKeysMapping[$key])) {
                $envUpdateData[$envKeysMapping[$key]] = $finalValue;
            }
        }

        // Batch update .env if needed
        if (! empty($envUpdateData)) {
            $this->updateEnv($envUpdateData);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
