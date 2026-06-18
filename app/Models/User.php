<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'email',
        'phone_number',
        'email_verified_at',
        'password',
        'user_type', // super_admin, owner, member
        'role',      // Support Manager, Support Agent, etc.
        'company_name',
        'status',
        'terms_accepted_at',
        'last_login_at',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_password_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'reset_password_token_expire_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */

    // The team owner (Subscriber)
    public function owner()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // The team members (Staff)
    public function members()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Get the OTPs for the user.
     */
    public function otps()
    {
        return $this->hasMany(Otp::class);
    }


    /**
     * Get the user's settings (pivot).
     */
    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * Get the tickets for the user (Owner).
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'owner_id');
    }

    /**
     * Get the tickets assigned to this user by name.
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned', 'name');
    }

    /**
     * Get the integrations for the user.
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    /**
     * Helper to get a setting value.
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();

        if ($setting && $key === 'secret_key' && $setting->value) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Exception $e) {
                return $setting->value;
            }
        }

        return $setting ? $setting->value : $default;
    }

    /**
     * Check if the user has an active subscription.
     */
    public function isSubscribed()
    {
        if ($this->user_type === 'owner') {
            return $this->subscribed('default');
        }

        if ($this->user_type === 'member' && $this->parent_id) {
            return $this->owner->isSubscribed();
        }

        return false;
    }

    /**
     * Get the root owner ID for the team.
     */
    public function getTeamOwnerId()
    {
        if ($this->user_type === 'owner') {
            return $this->id;
        }

        if ($this->parent_id) {
            $parent = $this->owner;
            if ($parent && $parent->user_type === 'owner') {
                return $parent->id;
            }
            return $parent ? $parent->getTeamOwnerId() : $this->parent_id;
        }
        return $this->id;
    }

    /**
     * Check if the AI settings are configured.
     */
    public function isAiConfigured(): bool
    {
        return !empty(env('OPENAI_API_KEY')) && !empty(env('AI_PROVIDER'));
    }

}
