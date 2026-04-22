<?php

namespace App\Repositories\Owner;

use App\Models\User;
use App\Models\UserSetting;

class SettingsRepository
{
    /**
     * Update or create a user setting.
     *
     * @param User $user
     * @param string $key
     * @param mixed $value
     * @return UserSetting
     */
    public function updateSetting(User $user, string $key, $value): UserSetting
    {
        return UserSetting::updateOrCreate(
            ['user_id' => $user->id, 'key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
    }

    /**
     * Update core user details.
     */
    public function updateCore(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }
}
