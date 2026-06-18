<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Models\AiAutomationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class AiAutomationSettingController extends Controller
{
    use ApiResponse;
    /**
     * Retrieve the AI Automation Settings for the authenticated owner.
     */
    public function show(Request $request)
    {
        $userId = $request->user()->getTeamOwnerId(); // Assuming getTeamOwnerId gets the owner context

        $setting = AiAutomationSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'mode' => 'copilot',
                'human_led_threshold' => 60,
                'ai_assisted_threshold' => 80,
            ]
        );

        return $this->sendResponse($this->buildResponse($setting), 'AI Automation settings retrieved successfully.');
    }

    /**
     * Update Mode and/or Thresholds (all optional)
     */
    public function update(Request $request)
    {
        $userId = $request->user()->getTeamOwnerId();

        $request->validate([
            'mode'                  => 'sometimes|in:supervised,copilot,autopilot',
            'human_led_threshold'   => 'sometimes|integer|min:0|max:99',
            'ai_assisted_threshold' => 'sometimes|integer|min:1|max:100|gt:human_led_threshold',
        ]);

        $setting = AiAutomationSetting::firstOrCreate(
            ['user_id' => $userId],
            ['mode' => 'copilot', 'human_led_threshold' => 60, 'ai_assisted_threshold' => 80]
        );

        $newMode = $request->input('mode', $setting->mode);
        $newHuman = $request->input('human_led_threshold', $setting->human_led_threshold);
        $newAi = $request->input('ai_assisted_threshold', $setting->ai_assisted_threshold);

        $modeSettings = $setting->mode_settings ?? [];
        $modeSettings[$newMode] = [
            'human_led_threshold' => $newHuman,
            'ai_assisted_threshold' => $newAi,
        ];

        $setting->update([
            'mode'                  => $newMode,
            'human_led_threshold'   => $newHuman,
            'ai_assisted_threshold' => $newAi,
            'mode_settings'         => $modeSettings
        ]);

        $setting->refresh();

        return $this->sendResponse($this->buildResponse($setting), 'Settings updated successfully.');
    }

    /**
     * Update only the Thresholds (Human-led % and AI-assisted %)
     */
    public function updateThresholds(Request $request)
    {
        $userId = $request->user()->getTeamOwnerId();

        $request->validate([
            'human_led_threshold' => 'required|integer|min:0|max:99',
            'ai_assisted_threshold' => 'required|integer|min:1|max:100|gt:human_led_threshold',
        ]);

        $setting = AiAutomationSetting::firstOrCreate(
            ['user_id' => $userId],
            ['mode' => 'copilot', 'human_led_threshold' => 60, 'ai_assisted_threshold' => 80]
        );

        $modeSettings = $setting->mode_settings ?? [];
        $modeSettings[$setting->mode] = [
            'human_led_threshold' => $request->human_led_threshold,
            'ai_assisted_threshold' => $request->ai_assisted_threshold,
        ];

        $setting->update([
            'human_led_threshold' => $request->human_led_threshold,
            'ai_assisted_threshold' => $request->ai_assisted_threshold,
            'mode_settings' => $modeSettings
        ]);
        $setting->refresh();

        return $this->sendResponse($this->buildResponse($setting), 'Thresholds updated successfully.');
    }

    /**
     * Build the full structured response
     */
    private function buildResponse($setting)
    {
        $modeSettings = $setting->mode_settings ?? [];
        
        $supHuman = $modeSettings['supervised']['human_led_threshold'] ?? 80;
        $supAi = $modeSettings['supervised']['ai_assisted_threshold'] ?? 95;
        
        $copHuman = $modeSettings['copilot']['human_led_threshold'] ?? 60;
        $copAi = $modeSettings['copilot']['ai_assisted_threshold'] ?? 80;
        
        $autoHuman = $modeSettings['autopilot']['human_led_threshold'] ?? 20;
        $autoAi = $modeSettings['autopilot']['ai_assisted_threshold'] ?? 60;

        return [
            'human_led_threshold'   => $setting->human_led_threshold,
            'ai_assisted_threshold' => $setting->ai_assisted_threshold,
            'last_updated_at'       => $setting->updated_at,
            'modes' => [
                [
                    'id' => 1, 
                    'name' => 'supervised', 
                    'title' => 'Supervised', 
                    'description' => 'AI acts only on very high confidence cases', 
                    'is_selected' => $setting->mode === 'supervised',
                    'zones' => $this->getZones($supHuman, $supAi)
                ],
                [
                    'id' => 2, 
                    'name' => 'copilot', 
                    'title' => 'Co-pilot', 
                    'description' => 'Balanced between AI and human review', 
                    'is_selected' => $setting->mode === 'copilot',
                    'zones' => $this->getZones($copHuman, $copAi)
                ],
                [
                    'id' => 3, 
                    'name' => 'autopilot', 
                    'title' => 'Autopilot', 
                    'description' => 'AI handles most tickets autonomously', 
                    'is_selected' => $setting->mode === 'autopilot',
                    'zones' => $this->getZones($autoHuman, $autoAi)
                ],
            ],
        ];
    }

    private function getZones($human_led, $ai_assisted)
    {
        return [
            ['id' => 1, 'name' => 'human_led', 'title' => 'Human-led', 'range' => '0-' . $human_led . '%', 'min' => 0, 'max' => $human_led, 'description' => 'Confidence too low to act. Your team handles the ticket directly.', 'action_text' => 'Your Team resolves'],
            ['id' => 2, 'name' => 'ai_assisted', 'title' => 'AI-assisted', 'range' => $human_led . '-' . $ai_assisted . '%', 'min' => $human_led, 'max' => $ai_assisted, 'description' => 'AI drafts a reply. Your agent reviews and approves before it is sent.', 'action_text' => 'Agent reviews AI reply'],
            ['id' => 3, 'name' => 'ai_driven', 'title' => 'AI-driven', 'range' => $ai_assisted . '-100%', 'min' => $ai_assisted, 'max' => 100, 'description' => 'AI is confident. Ticket resolved and replied to automatically.', 'action_text' => 'AI resolve automatically'],
        ];
    }
}
