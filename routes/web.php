<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\API\Owner\ShopifyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/owner/test-shopify', [\App\Http\Controllers\Owner\TestIntegrationController::class, 'index'])->name('owner.test-shopify');
    Route::get('/owner/shopify/customers', [\App\Http\Controllers\Owner\TestIntegrationController::class, 'listCustomers']);
    
    // Shopify OAuth Routes removed from web.php
    
    // Add this line for session-based connect (for test page)
    Route::post('/owner/integrations/{provider}/connect', [\App\Http\Controllers\API\Owner\IntegrationApiController::class, 'connect']);
});

Route::get('/shopify/install', [ShopifyController::class, 'install'])
    ->name('shopify.install')
    ->middleware('signed');

Route::get('/shopify/callback', [ShopifyController::class, 'callback'])
    ->name('shopify.callback');

Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook']);

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Owner Management
        Route::get('owners', [OwnerController::class, 'index'])->name('owners.index');
        // Transaction Management
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

        // Pricing Plans Management
        Route::resource('pricing-plans', PricingPlanController::class);
        Route::patch('pricing-plans/{pricingPlan}/toggle-active', [PricingPlanController::class, 'toggleActive'])->name('pricing-plans.toggle-active');

        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
            // General Settings
            Route::prefix('general')->name('general.')->group(function () {
                Route::get('profile', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile');
                Route::post('profile/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
                Route::post('profile/remove-picture', [App\Http\Controllers\Admin\ProfileController::class, 'removePicture'])->name('profile.remove-picture');
                Route::get('security', function () {
                    return Inertia::render('Admin/Settings/General/Security');
                })->name('security');
            });

            // Website Settings
            Route::prefix('website')->name('website.')->group(function () {
                Route::get('system', [SettingController::class, 'websiteSystem'])->name('system');
            });

            // System Settings
            Route::prefix('system')->name('system.')->group(function () {
                Route::get('email', [SettingController::class, 'emailSettings'])->name('email');
                Route::post('email/update', [SettingController::class, 'updateEmail'])->name('email.update');
            });

            // Financial Settings
            Route::prefix('financial')->name('financial.')->group(function () {
                Route::get('gateway', [SettingController::class, 'financialGateway'])->name('gateway');
            });

            // Generic Update Route
            Route::post('update', [SettingController::class, 'update'])->name('update');
        });
    });
});

require __DIR__.'/auth.php';
