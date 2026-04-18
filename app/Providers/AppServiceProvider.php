<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Override Config from Database
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::all()->pluck('value', 'key');

                if (isset($settings['site_name'])) {
                    config(['app.name' => $settings['site_name']]);
                }

                // For debug mode, we need to handle the string to boolean conversion
                if (isset($settings['debug_mode'])) {
                    config(['app.debug' => filter_var($settings['debug_mode'], FILTER_VALIDATE_BOOLEAN)]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if DB is not ready
        }
    }
}
