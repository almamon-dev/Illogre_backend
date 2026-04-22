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
use App\Models\Otp;
use App\Models\Payment;
use App\Models\PricingPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Repositories\RegistrationRepository;
use App\Services\Manager\ManagerService;
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

    protected $managerService;

    public function __construct(
        RegistrationRepository $registrationRepo,
        RegistrationService $registrationService,
        ManagerService $managerService
    ) {
        $this->registrationRepo = $registrationRepo;
        $this->registrationService = $registrationService;
        $this->managerService = $managerService;
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
                'terms_accepted_at' => $request->terms ? now() : null,
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

            $user->update(['last_login_at' => now()]);

            // Check if user is an owner and has an active subscription
            if ($user->user_type === 'owner' && ! $user->isSubscribed()) {
                return $this->sendResponse([
                    'user' => new LoginResource($user),
                    'is_subscribed' => false,
                    'next_step' => 'checkout',
                ], 'Subscription required. Please complete payment.');
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
                return $this->sendResponse(new LoginResource($user), 'Already registered. Please login.');
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
            // Create the user account but DO NOT return an access token yet.
            $user = $this->registrationService->finalizeFromCache($email);
            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'OTP verified and account created. Please proceed to payment to activate your subscription.', $token);

        } catch (Exception $e) {
            return $this->sendError('Verification failed', [$e->getMessage()], 500);
        }
    }



    public function initiateCheckout(Request $request): JsonResponse
    {
        try {
            $email = $request->email;
            $user = auth()->user() ?: $this->registrationRepo->findUserByEmail($email);

            if (! $user) {
                return $this->sendError('User not found. Please register and verify your email first.', [], 404);
            }

            $planId = $request->pricing_plan_id;
            if (! $planId) {
                return $this->sendError('Pricing plan is required.', [], 422);
            }

            $plan = PricingPlan::findOrFail($planId);
            $session = $this->registrationService->createCheckoutSession($user->email, $plan);

            // Create pending payment record
            Payment::create([
                'user_id' => $user->id,
                'pricing_plan_id' => $plan->id,
                'external_payment_id' => $session->id,
                'amount' => $plan->price,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_method' => 'card',
            ]);

            // Create pending subscription record
            UserSubscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'pricing_plan_id' => $plan->id,
                    'status' => 'pending',
                    'is_trial' => false,
                    'started_at' => now(),
                    'expires_at' => now()->addMonth(),
                ]
            );

            return $this->sendResponse([
                'email' => $user->email,
                'checkout_url' => $session->url,
                'is_subscribed' => $user->isSubscribed(),
            ], 'Checkout ready.');

        } catch (Exception $e) {
            return $this->sendError('Checkout failed: '.$e->getMessage(), [], 500);
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
                'next_step' => 'reset_password',
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

    /**
     * Accept a team invitation and activate the account.
     */
    public function acceptInvitation($token): JsonResponse
    {
        try {
            $email = base64_decode($token);

            $user = User::where('email', $email)
                ->where('status', 'invited')
                ->first();

            if (! $user) {
                return $this->sendError('Invalid or expired invitation.', [], 404);
            }

            // Activate user and sync business name from owner
            $owner = $user->owner;

            $user->update([
                'status' => 'active',
                'email_verified_at' => now(),
                'company_name' => $owner ? $owner->company_name : $user->company_name,
                'last_login_at' => now(),
                'last_active_at' => now(),
                'terms_accepted_at' => now(),
            ]);

            return $this->sendResponse([
                'email' => $user->email,
                'message' => 'Invitation accepted successfully. You can now login with your temporary password.',
            ], 'Account activated.');

        } catch (Exception $e) {
            return $this->sendError('Failed to accept invitation.', [$e->getMessage()], 500);
        }
    }

    /**
     * Accept a Support Agent invitation.
     */
    public function acceptAgentInvitation(Request $request): JsonResponse
    {
        try {
            $token = $request->query('token');
            if (! $token) {
                return $this->sendError('Invitation token is required.', [], 400);
            }

            $user = $this->managerService->acceptInvitation($token);

            $token = $user->createToken('AuthToken')->plainTextToken;

            return $this->sendResponse(new LoginResource($user), 'Invitation accepted and account activated successfully.', $token);
        } catch (Exception $e) {
            return $this->sendError('Failed to accept invitation.', [$e->getMessage()]);
        }
    }
}
