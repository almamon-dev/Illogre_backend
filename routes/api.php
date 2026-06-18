<?php

use App\Http\Controllers\API\Admin\PricingPlanController;
use App\Http\Controllers\API\Agent\TicketAiController;
use App\Http\Controllers\API\Agent\TicketController;
use App\Http\Controllers\API\Auth\AuthApiController;
use App\Http\Controllers\API\InboundEmailController;
use App\Http\Controllers\API\Manager\AgentController;
use App\Http\Controllers\API\Owner\AiAutomationSettingController;
use App\Http\Controllers\API\Owner\AutomationRuleController;
use App\Http\Controllers\API\Owner\BillingController;
use App\Http\Controllers\API\Owner\CustomerController;
use App\Http\Controllers\API\Owner\DashboardController;
use App\Http\Controllers\API\Owner\IntegrationApiController;
use App\Http\Controllers\API\Owner\KnowledgeSourceApiController;
use App\Http\Controllers\API\Owner\SettingsApiController;
use App\Http\Controllers\API\Owner\SlaPolicyController;
use App\Http\Controllers\API\Owner\TeamController;
use App\Http\Controllers\API\PricingPlanApiController;
use App\Http\Controllers\API\ShopifyWebhookController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Public APIs ---
Route::get('/pricing-plans',               [PricingPlanApiController::class, 'index']);
Route::get('/owner/shopify/callback',      [IntegrationApiController::class, 'shopifyCallback'])->name('api.owner.shopify.callback');

// --- Webhooks ---
Route::prefix('webhooks')->group(function () {
    Route::post('/inbound-email',          [InboundEmailController::class, 'handle']);
    Route::post('/stripe',                 [StripeWebhookController::class, 'handleWebhook']);
    Route::post('/shopify/customers',      [ShopifyWebhookController::class, 'handleCustomers'])->name('api.webhooks.shopify.customers');
    Route::post('/shopify/orders',         [ShopifyWebhookController::class, 'handleOrders'])->name('api.webhooks.shopify.orders');
});

// --- Auth Flow ---
Route::prefix('auth')->group(function () {
    Route::prefix('register')->group(function () {
        Route::post('/',                   [AuthApiController::class, 'registerApi']);
        Route::post('/verify-otp',         [AuthApiController::class, 'verifyRegistrationOtp']);
        Route::post('/resend-otp',         [AuthApiController::class, 'resendOtpApi']);
        Route::post('/checkout',           [AuthApiController::class, 'initiateCheckout']);
    });

    Route::post('/login',                  [AuthApiController::class, 'loginApi']);
    Route::post('/forgot-password',        [AuthApiController::class, 'forgotPasswordApi']);
    Route::post('/verify-otp',             [AuthApiController::class, 'verifyOtpApi']);
    Route::post('/reset-password',         [AuthApiController::class, 'resetPasswordApi']);
    Route::post('/accept-invitation/{t}',  [AuthApiController::class, 'acceptInvitation']);
    Route::get('/accept-agent-invitation', [AuthApiController::class, 'acceptAgentInvitation']);
});

// --- Authenticated APIs ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Status
    Route::get('/user',                    fn(Request $request) => $request->user());
    Route::post('/auth/logout',            [AuthApiController::class, 'logoutApi']);

    // Admin (Super Admin)
    Route::prefix('admin')->group(function () {
        Route::post('/pricing-plans',      [PricingPlanController::class, 'store']);
    });

    // Owner (Team Subscriber)
    Route::prefix('owner')->middleware(['owner'])->group(function () {
        Route::get('/dashboard/overview',  [DashboardController::class, 'index']);
        Route::post('/shopify/install',    [IntegrationApiController::class, 'shopifyInstall'])->middleware(['subscribed']);
        
        Route::prefix('billing')->group(function () {
            Route::get('/overview',        [BillingController::class, 'index']);
            Route::post('/checkout',       [BillingController::class, 'checkout']);
            Route::post('/portal',         [BillingController::class, 'portal']);
            Route::post('/cancel',         [BillingController::class, 'cancel']);
            Route::post('/resume',         [BillingController::class, 'resume']);
            Route::post('/swap',           [BillingController::class, 'swap']);
        });

        Route::prefix('settings')->group(function () {
            Route::get('/',                [SettingsApiController::class, 'index']);
            Route::patch('/general',       [SettingsApiController::class, 'updateGeneral']);
            Route::patch('/workspace',     [SettingsApiController::class, 'updateWorkspace']);
            Route::patch('/ai',            [SettingsApiController::class, 'updateAI']);
            Route::patch('/notifications', [SettingsApiController::class, 'updateNotifications']);
            Route::patch('/security',      [SettingsApiController::class, 'updateSecurity']);
        });

        // Features requiring active subscription
        Route::middleware(['subscribed'])->group(function () {
            Route::prefix('team')->group(function () {
                Route::get('/',            [TeamController::class, 'index']);
                Route::post('/invite',     [TeamController::class, 'invite']);
                Route::patch('/{id}',      [TeamController::class, 'update']);
                Route::delete('/{id}',     [TeamController::class, 'destroy']);
            });

            Route::prefix('customers')->middleware(['ai_configured'])->group(function () {
                Route::get('/',            [CustomerController::class, 'index']);
                Route::post('/',           [CustomerController::class, 'store']);
                Route::get('/{id}',        [CustomerController::class, 'show']);
                Route::patch('/{id}',      [CustomerController::class, 'update']);
                Route::delete('/{id}',     [CustomerController::class, 'destroy']);
            });

            Route::prefix('knowledge-base')->middleware(['ai_configured'])->group(function () {
                Route::get('/',            [KnowledgeSourceApiController::class, 'index']);
                Route::post('/',           [KnowledgeSourceApiController::class, 'store']);
                Route::delete('/{kSource}',[KnowledgeSourceApiController::class, 'destroy']);
            });

            Route::prefix('automations')->middleware(['ai_configured'])->group(function () {
                Route::apiResource('rules',AutomationRuleController::class);
                Route::apiResource('slas', SlaPolicyController::class);
                Route::get('/settings',    [AiAutomationSettingController::class, 'show']);
                Route::post('/settings',   [AiAutomationSettingController::class, 'update']);
            });

            Route::prefix('integrations')->group(function () {
                Route::get('/',            [IntegrationApiController::class, 'index']);
                Route::post('/connect',    [IntegrationApiController::class, 'connect']);
                Route::post('/{p}/connect',[IntegrationApiController::class, 'connect']);
                Route::delete('/{id}',     [IntegrationApiController::class, 'disconnect']);
            });
        });
    });

    // Support Manager
    Route::prefix('manager')->middleware(['support_manager', 'subscribed'])->group(function () {
        Route::prefix('agents')->group(function () {
            Route::get('/',                [AgentController::class, 'index']);
            Route::post('/',               [AgentController::class, 'store']);
            Route::put('/{id}',            [AgentController::class, 'update']);
            Route::delete('/{id}',         [AgentController::class, 'destroy']);
        });

        Route::prefix('automations')->middleware(['ai_configured'])->group(function () {
            Route::get('/settings',        [AiAutomationSettingController::class, 'show']);
            Route::post('/settings',       [AiAutomationSettingController::class, 'update']);
        });

        Route::prefix('customers')->middleware(['ai_configured'])->group(function () {
            Route::get('/',                [CustomerController::class, 'index']);
            Route::post('/',               [CustomerController::class, 'store']);
            Route::get('/{id}',            [CustomerController::class, 'show']);
            Route::patch('/{id}',          [CustomerController::class, 'update']);
            Route::delete('/{id}',         [CustomerController::class, 'destroy']);
        });

        Route::prefix('knowledge-base')->middleware(['ai_configured'])->group(function () {
            Route::get('/',                [KnowledgeSourceApiController::class, 'index']);
            Route::post('/',               [KnowledgeSourceApiController::class, 'store']);
            Route::delete('/{kSource}',    [KnowledgeSourceApiController::class, 'destroy']);
        });
    });

    // Support Agent (View Only)
    Route::prefix('agent')->middleware(['support_agent', 'subscribed'])->group(function () {
        Route::prefix('customers')->middleware(['ai_configured'])->group(function () {
            Route::get('/',                [CustomerController::class, 'index']);
            Route::get('/{id}',            [CustomerController::class, 'show']);
        });
    });

    // Shared Team Routes (Owner, Manager, Agent)
    Route::prefix('shared')->middleware(['subscribed'])->group(function () {
        Route::prefix('tickets')->middleware(['ai_configured'])->group(function () {
            Route::get('/',                [TicketController::class, 'index']);
            Route::get('/{id}',            [TicketController::class, 'show']);
            
            Route::post('/{id}/ai-reply',  [TicketAiController::class, 'generateSuggestedReply']);
            Route::post('/{id}/reply',     [TicketAiController::class, 'sendReply']);
        });
    });
});
