<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterApiRequest;
use App\Http\Requests\API\Auth\VerifyRegistrationOtpRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\Otp;
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
use Illuminate\Support\Str;

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

    public function loginApi(LoginRequest $request): JsonResponse
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
                $token = $user->createToken('AuthToken')->plainTextToken;

                return $this->sendResponse(new LoginResource($user), 'Already registered.', $token);
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
            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'Registration successful!', $token);

        } catch (Exception $e) {
            return $this->sendError('Verification failed', [], 500);
        }
    }

    public function verifyEmailApi(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $user = $this->registrationRepo->findUserByEmail($request->email);
            if (! $user) {
                return $this->sendError('User not found.', [], 404);
            }

            $otp = Otp::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->where('purpose', 'Verification')
                ->where('is_verified', false)
                ->where('expires_at', '>', now())
                ->first();

            if (! $otp) {
                return $this->sendError('Invalid or expired OTP.', [], 422);
            }

            $user->update(['email_verified_at' => now()]);
            $otp->update(['is_verified' => true]);

            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'Email verified successfully.', $token);

        } catch (Exception $e) {
            return $this->sendError('Verification failed.', [], 500);
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
                $token = $user->createToken('AuthToken')->plainTextToken;

                return $this->sendResponse(new LoginResource($user), 'Registration completed.', $token);
            }

            $userData = $this->registrationRepo->getCachedRegistration($email);
            if (! $userData || ! ($userData['email_verified'] ?? false)) {
                return $this->sendError('Invalid session.', [], 403);
            }

            $user = $this->registrationService->finalizeFromCache($email);
            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'Finalized successfully.', $token);

        } catch (Exception $e) {
            return $this->sendError('Finalization failed', [], 500);
        }
    }

    public function resendOtpApi(ResendOtpRequest $request): JsonResponse
    {
        try {
            $email = $request->email;

            // 1. Check if user exists in DB
            $user = $this->registrationRepo->findUserByEmail($email);
            if ($user) {
                $this->sendOtp($user, $request->purpose ?? 'Resend OTP');

                return $this->sendResponse(['email' => $user->email], 'OTP resent.');
            }

            // 2. Check if user is in registration cache
            $userData = $this->registrationRepo->getCachedRegistration($email);
            if ($userData) {
                $otp = $this->sendOtpToEmail($email, $userData['name'], 'Verify Your Email Address');
                $this->registrationRepo->storeCachedOtp($email, $otp);

                return $this->sendResponse(['email' => $email], 'Account verification code resent.');
            }

            return $this->sendError('User not found or session expired.', [], 404);

        } catch (Exception $e) {
            return $this->sendError('Failed to resend OTP: '.$e->getMessage(), [], 500);
        }
    }

    public function forgotPasswordApi(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = $this->registrationRepo->findUserByEmail($request->email);
            if (! $user) {
                return $this->sendError('User not found.', [], 404);
            }

            $this->sendOtp($user, 'Reset Password');

            return $this->sendResponse(['email' => $user->email], 'OTP sent for password reset.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function verifyOtpApi(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $user = $this->registrationRepo->findUserByEmail($request->email);
            if (! $user) {
                return $this->sendError('User not found.', [], 404);
            }

            $otp = Otp::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->where('purpose', 'Reset Password')
                ->where('is_verified', false)
                ->where('expires_at', '>', now())
                ->first();

            if (! $otp) {
                return $this->sendError('Invalid or expired OTP.', [], 422);
            }

            // Mark OTP as verified and generate a reset token
            $rawToken = Str::random(60);
            $otp->update([
                'is_verified' => true,
                'verified_at' => now(),
                'token' => hash('sha256', $rawToken),
            ]);

            return $this->sendResponse([
                'email' => $user->email,
                'token' => $rawToken,
                'next_step' => 'reset_password'
            ], 'OTP verified successfully.');

        } catch (Exception $e) {
            return $this->sendError('Verification failed.', [], 500);
        }
    }

    public function resetPasswordApi(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $user = $this->registrationRepo->findUserByEmail($request->email);
            if (! $user) {
                return $this->sendError('User not found.', [], 404);
            }
            $otp = Otp::where('user_id', $user->id)
                ->where('token', hash('sha256', $request->token))
                ->where('purpose', 'Reset Password')
                ->where('is_verified', true) // It was verified in the previous step
                ->where('expires_at', '>', now())
                ->first();

            if (! $otp) {
                return $this->sendError('Invalid or expired session.', [], 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            $otp->update(['is_verified' => true]);

            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'Password reset successful.', $token);

        } catch (Exception $e) {
            return $this->sendError('Password reset failed.', [], 500);
        }
    }

    public function logoutApi(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'Logout successful.');
    }
}
