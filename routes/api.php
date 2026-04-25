<?php

use App\Http\Controllers\API\Admin\PricingPlanController;
use App\Http\Controllers\API\Agent\TicketController;
use App\Http\Controllers\API\Auth\AuthApiController;
use App\Http\Controllers\API\Manager\AgentController;
use App\Http\Controllers\API\Owner\BillingController;
use App\Http\Controllers\API\Owner\CustomerController;
use App\Http\Controllers\API\Owner\DashboardController;
use App\Http\Controllers\API\Owner\KnowledgeSourceApiController;
use App\Http\Controllers\API\Owner\SettingsApiController;
use App\Http\Controllers\API\Owner\TeamController;
use App\Http\Controllers\API\PricingPlanApiController;
use App\Http\Controllers\API\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Pricing Plans
Route::get('/pricing-plans', [PricingPlanApiController::class, 'index']);

// Public Auth Routes
Route::prefix('auth')->group(function () {
    // Registration Flow
    Route::prefix('register')->group(function () {
        Route::post('/', [AuthApiController::class, 'registerApi']);
        Route::post('/verify-otp', [AuthApiController::class, 'verifyRegistrationOtp']);
        Route::post('/resend-otp', [AuthApiController::class, 'resendOtpApi']);
        Route::post('/checkout', [AuthApiController::class, 'initiateCheckout']);
    });

    // Login
    Route::post('/login', [AuthApiController::class, 'loginApi']);

    // Password Reset Flow
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPasswordApi']);
    Route::post('/verify-otp', [AuthApiController::class, 'verifyOtpApi']);
    Route::post('/reset-password', [AuthApiController::class, 'resetPasswordApi']);

    // Invitations
    Route::post('/accept-invitation/{token}', [AuthApiController::class, 'acceptInvitation']);
    Route::get('/accept-agent-invitation', [AuthApiController::class, 'acceptAgentInvitation']);
});

// Stripe Webhook
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logoutApi']);

    // Owner Dashboard
    Route::prefix('owner')->middleware(['owner'])->group(function () {
        Route::get('/dashboard/overview', [DashboardController::class, 'index']);
        Route::get('/billing/overview', [BillingController::class, 'index']);

        // Team Management (Requires Subscription)
        Route::middleware(['subscribed'])->group(function () {
            Route::get('/team', [TeamController::class, 'index']);
            Route::post('/team/invite', [TeamController::class, 'invite']);
            Route::patch('/team/{id}', [TeamController::class, 'update']);
            Route::delete('/team/{id}', [TeamController::class, 'destroy']);
        });

        // Settings Management
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsApiController::class, 'index']);
            Route::patch('/general', [SettingsApiController::class, 'updateGeneral']);
            Route::patch('/workspace', [SettingsApiController::class, 'updateWorkspace']);
            Route::patch('/ai', [SettingsApiController::class, 'updateAI']);
            Route::patch('/notifications', [SettingsApiController::class, 'updateNotifications']);
            Route::patch('/security', [SettingsApiController::class, 'updateSecurity']);
        });
        // Customer Management
        Route::prefix('customers')->middleware(['subscribed'])->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('/{id}', [CustomerController::class, 'show']);
            Route::patch('/{id}', [CustomerController::class, 'update']);
            Route::delete('/{id}', [CustomerController::class, 'destroy']);
        });

        // Knowledge Base (Requires Subscription)
        Route::prefix('knowledge-base')->middleware(['subscribed'])->group(function () {
            Route::get('/', [KnowledgeSourceApiController::class, 'index']);
            Route::post('/', [KnowledgeSourceApiController::class, 'store']);
            Route::delete('/{knowledgeSource}', [KnowledgeSourceApiController::class, 'destroy']);
        });
    });

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::post('/pricing-plans', [PricingPlanController::class, 'store']);
    });

    // Support Manager Routes (Requires Subscription from Owner)
    Route::prefix('manager')->middleware(['support_manager', 'subscribed'])->group(function () {
        Route::get('/agents', [AgentController::class, 'index']);
        Route::post('/agents', [AgentController::class, 'store']);
        Route::put('/agents/{id}', [AgentController::class, 'update']);
        Route::delete('/agents/{id}', [AgentController::class, 'destroy']);
    });
    // Support Agent Routes
    Route::prefix('agent')->middleware(['support_agent', 'subscribed'])->group(function () {
        Route::get('/tickets', [TicketController::class, 'index']);
        
        // Agent Customer Access
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
    });
});
