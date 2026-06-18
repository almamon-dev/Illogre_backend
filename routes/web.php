<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\API\Owner\IntegrationApiController;
use App\Http\Controllers\API\Owner\ShopifyController;
use App\Http\Controllers\Owner\TestIntegrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Public Webhooks & Callbacks ---
Route::get('/shopify/install',     [ShopifyController::class, 'install'])->name('shopify.install')->middleware('signed');
Route::get('/shopify/callback',    [ShopifyController::class, 'callback'])->name('shopify.callback');
Route::post('/stripe/webhook',     [StripeWebhookController::class, 'handleWebhook']);

// --- Authenticated Web Routes ---
Route::middleware('auth')->group(function () {

    // General Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',            [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',          [ProfileController::class, 'update'])->name('update');
        Route::delete('/',         [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Verified Access
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard',   [DashboardController::class, 'index'])->name('dashboard');

        // Owner Testing
        Route::prefix('owner')->name('owner.')->group(function () {
            Route::get('/test-shopify',                       [TestIntegrationController::class, 'index'])->name('test-shopify');
            Route::get('/shopify/customers',                  [TestIntegrationController::class, 'listCustomers']);
            Route::post('/integrations/{provider}/connect',   [IntegrationApiController::class, 'connect']);
        });
    });

    // Super Admin Portal
    Route::prefix('admin')->name('admin.')->group(function () {
        
        Route::get('owners',       [OwnerController::class, 'index'])->name('owners.index');
        
        Route::resource('pricing-plans', PricingPlanController::class);
        Route::patch('pricing-plans/{pricingPlan}/toggle-active', [PricingPlanController::class, 'toggleActive'])->name('pricing-plans.toggle-active');

        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/',        [TransactionController::class, 'index'])->name('index');
            Route::get('/{txn}',   [TransactionController::class, 'show'])->name('show');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            
            Route::prefix('general')->name('general.')->group(function () {
                Route::get('profile',                [AdminProfileController::class, 'index'])->name('profile');
                Route::post('profile/update',        [AdminProfileController::class, 'update'])->name('profile.update');
                Route::post('profile/remove-picture',[AdminProfileController::class, 'removePicture'])->name('profile.remove-picture');
                Route::get('security',               fn() => Inertia::render('Admin/Settings/General/Security'))->name('security');
            });

            Route::prefix('website')->name('website.')->group(function () {
                Route::get('system',                 [SettingController::class, 'websiteSystem'])->name('system');
            });

            Route::prefix('system')->name('system.')->group(function () {
                Route::get('email',                  [SettingController::class, 'emailSettings'])->name('email');
                Route::post('email/update',          [SettingController::class, 'updateEmail'])->name('email.update');
                
                Route::get('ai',                     [SettingController::class, 'aiSettings'])->name('ai');
                Route::post('ai/update',             [SettingController::class, 'updateAiSettings'])->name('ai.update');
            });

            Route::prefix('financial')->name('financial.')->group(function () {
                Route::get('gateway',                [SettingController::class, 'financialGateway'])->name('gateway');
            });

            Route::post('update',                    [SettingController::class, 'update'])->name('update');
        });
    });
});

require __DIR__.'/auth.php';
