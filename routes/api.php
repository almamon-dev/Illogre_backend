<?php

use App\Http\Controllers\API\Auth\AuthApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Pricing Plans
Route::get('/pricing-plans', [\App\Http\Controllers\API\PricingPlanApiController::class, 'index']);

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthApiController::class, 'registerApi']);
    Route::post('/register/verify-otp', [AuthApiController::class, 'verifyRegistrationOtp']);
    Route::post('/register/resend-otp', [AuthApiController::class, 'resendRegistrationOtp']);
    Route::post('/register/checkout', [AuthApiController::class, 'initiateCheckout']);
    Route::post('/register/finalize', [AuthApiController::class, 'finalizeRegistration']);

    Route::post('/login', [AuthApiController::class, 'loginApi']);


    Route::post('/verify-email', [AuthApiController::class, 'verifyEmailApi']);
    Route::post('/resend-otp', [AuthApiController::class, 'resendOtpApi']);
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPasswordApi']);
    Route::post('/verify-otp', [AuthApiController::class, 'verifyOtpApi']);
    Route::post('/reset-password', [AuthApiController::class, 'resetPasswordApi']);
});

// Stripe Webhook
Route::post('/webhooks/stripe', [\App\Http\Controllers\API\StripeWebhookController::class, 'handle']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logoutApi']);
});
