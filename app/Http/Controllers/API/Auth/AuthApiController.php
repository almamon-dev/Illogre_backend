<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterApiRequest;
use App\Http\Requests\API\Auth\VerifyRegistrationOtpRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\PricingPlan;
use App\Repositories\RegistrationRepository;
use App\Services\RegistrationService;
use App\Traits\ApiResponse;
use App\Traits\SendOtp;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class AuthApiController extends Controller
{
    use ApiResponse, SendOtp;

    protected $registrationRepo;

    protected $registrationService;

    public function __construct(RegistrationRepository $registrationRepo, RegistrationService $registrationService)
    {
        $this->registrationRepo = $registrationRepo;
        $this->registrationService = $registrationService;
    }

    public function registerApi(RegisterApiRequest $request): JsonResponse
    {
        try {
            $userData = [
                'name' => $request->name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type ?? 'owner',
                'pricing_plan_id' => $request->pricing_plan_id,
                'step' => 1,
                'email_verified' => false,
            ];

            $this->registrationRepo->storeCachedRegistration($request->email, $userData);

            $otp = $this->sendOtpToEmail($request->email, $request->name, 'Verify Your Email Address');
            $this->registrationRepo->storeCachedOtp($request->email, $otp);

            return $this->sendResponse([
                'email' => $request->email,
                'message' => __('We’ve sent a verification code to your email.'),
                'next_step' => 'verify_otp',
            ], 'Registration initiated.');

        } catch (Exception $e) {
            Log::error('Registration Init Error: '.$e->getMessage());

            return $this->sendError('Registration failed', [], 500);
        }
    }

    public function loginApi(Request $request): JsonResponse
    {
        try {
            $user = $this->registrationRepo->findUserByEmail($request->email);

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return $this->sendError('Invalid Credentials', [], 401);
            }

            if (! $user->email_verified_at) {
                return $this->sendError('Email Not Verified', [], 403);
            }

            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'Login successful', $token);

        } catch (Exception $e) {
            return $this->sendError('Login failed', [], 500);
        }
    }

    public function verifyRegistrationOtp(VerifyRegistrationOtpRequest $request): JsonResponse
    {
        try {
            $email = $request->email;

            if ($user = $this->registrationRepo->findUserByEmail($email)) {
                return $this->sendResponse(new RegisterResource($user), 'Already registered.');
            }

            $cachedOtp = $this->registrationRepo->getCachedOtp($email);
            if (! $cachedOtp || $cachedOtp != $request->otp) {
                return $this->sendError('Invalid or expired OTP', [], 422);
            }

            $this->registrationRepo->clearCachedOtp($email);

            $userData = $this->registrationRepo->getCachedRegistration($email);
            if (! $userData) {
                return $this->sendError('Session expired.', [], 404);
            }

            $userData['email_verified'] = true;
            $this->registrationRepo->storeCachedRegistration($email, $userData);

            $plan = PricingPlan::find($userData['pricing_plan_id']);
            if ($plan && $plan->price > 0) {
                return $this->sendResponse(['email' => $email, 'next_step' => 'payment'], 'Email verified.');
            }

            $user = $this->registrationService->finalizeFromCache($email);

            return $this->sendResponse(new RegisterResource($user), 'Registration successful!');

        } catch (Exception $e) {
            return $this->sendError('Verification failed', [], 500);
        }
    }

    public function initiateCheckout(Request $request): JsonResponse
    {
        try {
            $email = $request->email;

            if ($user = $this->registrationRepo->findUserByEmail($email)) {
                return $this->sendResponse(new RegisterResource($user), 'Already registered.');
            }

            $userData = $this->registrationRepo->getCachedRegistration($email);
            if (! $userData || ! ($userData['email_verified'] ?? false)) {
                return $this->sendError('Unauthorized or expired session.', [], 403);
            }

            $plan = PricingPlan::findOrFail($userData['pricing_plan_id']);
            $session = $this->registrationService->createCheckoutSession($email, $plan);

            return $this->sendResponse(['email' => $email, 'checkout_url' => $session->url], 'Checkout ready.');


        } catch (Exception $e) {
            return $this->sendError('Checkout failed', [], 500);
        }
    }

    public function finalizeRegistration(Request $request): JsonResponse
    {
        try {
            $email = $request->email;

            if ($user = $this->registrationRepo->findUserByEmail($email)) {
                return $this->sendResponse(new RegisterResource($user), 'Registration completed.');
            }

            $userData = $this->registrationRepo->getCachedRegistration($email);
            if (! $userData || ! ($userData['email_verified'] ?? false)) {
                return $this->sendError('Invalid session.', [], 403);
            }

            $user = $this->registrationService->finalizeFromCache($email);

            return $this->sendResponse(new RegisterResource($user), 'Finalized successfully.');

        } catch (Exception $e) {
            return $this->sendError('Finalization failed', [], 500);
        }
    }

    public function resendOtpApi(ResendOtpRequest $request): JsonResponse
    {
        $user = $this->registrationRepo->findUserByEmail($request->email);
        $this->SendOtp($user, 'Resend OTP');

        return $this->sendResponse(new RegisterResource($user), 'OTP resent.');
    }

    public function forgotPasswordApi(ForgotPasswordRequest $request): JsonResponse
    {
        $user = $this->registrationRepo->findUserByEmail($request->email);
        $this->SendOtp($user, 'Reset Password');

        return $this->sendResponse(new RegisterResource($user), 'OTP sent.');
    }

    public function logoutApi(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'Logout successful.');
    }
}
