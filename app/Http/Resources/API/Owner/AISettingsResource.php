<?php

namespace App\Http\Resources\API\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AISettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'settings' => [
                'ai_tone' => $this->getSetting('ai_tone', 'professional'),
                'ai_agent_name' => $this->getSetting('ai_agent_name', 'Tixolve AI'),
                'ai_response_language' => $this->getSetting('ai_response_language', 'Auto-detect customer language'),
                'ai_enable_auto_response' => (bool)$this->getSetting('ai_enable_auto_response', false),
                'ai_require_human_approval' => (bool)$this->getSetting('ai_require_human_approval', true),
            ],
            'model_info' => [
                'ai_provider' => env('AI_PROVIDER', $this->getSetting('ai_provider', 'openai')),
                'ai_model' => env('AI_MODEL', $this->getSetting('ai_model', 'gpt-4o')),
                'latest_update' => $this->getSetting('ai_last_updated', now()->format('M j, Y')),
            ]
        ];
    }
}
