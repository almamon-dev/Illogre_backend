<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAutomationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'mode',
        'human_led_threshold',
        'ai_assisted_threshold',
    ];

    /**
     * Get the owner of this setting.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
