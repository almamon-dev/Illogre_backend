<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RegistrationRepository
{

    public function findUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function getCachedRegistration(string $email)
    {
        return Cache::get("reg_{$email}");
    }

    public function storeCachedRegistration(string $email, array $data)
    {
        Cache::put("reg_{$email}", $data, now()->addHours(2));
    }

    public function getCachedOtp(string $email)
    {
        return Cache::get("otp_{$email}");
    }

    public function storeCachedOtp(string $email, string $otp)
    {
        Cache::put("otp_{$email}", $otp, now()->addMinutes(30));
    }

    public function clearCachedOtp(string $email)
    {
        Cache::forget("otp_{$email}");
    }
}
