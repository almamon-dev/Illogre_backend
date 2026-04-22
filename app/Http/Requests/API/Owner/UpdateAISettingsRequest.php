<?php

namespace App\Http\Requests\API\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAISettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ai_tone' => 'required|in:friendly,professional,formal',
            'ai_agent_name' => 'required|string|max:255',
            'ai_response_language' => 'required|string|max:100',
            'secret_key' => 'nullable|string|max:500',
            'ai_enable_auto_response' => 'sometimes|boolean',
            'ai_require_human_approval' => 'sometimes|boolean',
            'ai_provider' => 'nullable|string|max:100',
            'ai_model' => 'nullable|string|max:100',
        ];
    }
}
