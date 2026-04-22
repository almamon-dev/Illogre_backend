<?php

namespace App\Services\Owner;

use App\Models\User;
use App\Repositories\Owner\SettingsRepository;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    protected $settingsRepository;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Update any settings for the owner.
     * This handles both core fields (in users table) and dynamic fields (in pivot).
     */
    public function updateSettings(User $user, array $data): User
    {
        // Core fields for the users table
        $coreFields = ['name', 'email', 'phone_number', 'company_name'];
        
        $toUpdate = [];
        foreach ($data as $key => $value) {
            // Handle File Upload for Logo
            if ($key === 'logo' && request()->hasFile('logo')) {
                $path = Helper::uploadFile('logos', request()->file('logo'));
                if ($path) {
                    $this->settingsRepository->updateSetting($user, 'brand_logo', $path);
                }
                continue;
            }

            // Handle File Upload for Avatar
            if ($key === 'avatar' && request()->hasFile('avatar')) {
                $path = Helper::uploadFile('avatars', request()->file('avatar'));
                if ($path) {
                    $this->settingsRepository->updateSetting($user, 'avatar_url', $path);
                }
                continue;
            }

            if (in_array($key, $coreFields)) {

                $toUpdate[$key] = $value;
            } else {
                // Encrypt secret_key before saving
                if ($key === 'secret_key' && !empty($value)) {
                    $value = Crypt::encryptString($value);
                }

                // Everything else goes to the pivot table
                $this->settingsRepository->updateSetting($user, $key, $value);
            }
        }

        // Track AI Settings Last Update
        $aiFields = ['ai_tone', 'ai_agent_name', 'ai_response_language', 'secret_key', 'ai_enable_auto_response', 'ai_require_human_approval', 'ai_provider', 'ai_model'];
        $hasAiUpdate = false;
        foreach ($data as $key => $val) {
            if (in_array($key, $aiFields)) {
                $hasAiUpdate = true;
                break;
            }
        }

        if ($hasAiUpdate) {
            $this->settingsRepository->updateSetting($user, 'ai_last_updated', now()->format('M j, Y'));
        }

        if (!empty($toUpdate)) {
            $this->settingsRepository->updateCore($user, $toUpdate);
        }

        return $user->fresh('settings');
    }

    /**
     * Update account security (password).
     */
    public function updateSecurity(User $user, array $data): bool
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Current password does not match.', 422);
        }

        return (bool) $this->settingsRepository->updateCore($user, [
            'password' => $data['new_password']
        ]);
    }
}
