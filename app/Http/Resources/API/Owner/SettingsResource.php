<?php

namespace App\Http\Resources\API\Owner;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Helper to get from pivot
        $get = fn ($key, $default = null) => $this->getSetting($key, $default);

        return [
            'personal_details' => [
                'name' => $this->name,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'avatar_url' => Helper::generateURL($get('avatar_url')),
                'is_subscribed' => $this->isSubscribed(),
            ],
            'company_details' => [
                'company_name' => $this->company_name,
                'support_email' => $get('support_email'),
                'website_url' => $get('website_url'),
                'brand_logo' => Helper::generateURL($get('brand_logo')),
            ],

            'ai_settings' => [
                'ai_tone' => $get('ai_tone', 'professional'),
                'ai_agent_name' => $get('ai_agent_name', 'Tixolve AI'),
                'ai_response_language' => $get('ai_response_language', 'Auto-detect customer language'),
                'secret_key' => $get('secret_key') ? '********************************' : null,
                'ai_enable_auto_response' => (bool) $get('ai_enable_auto_response', false),
                'ai_require_human_approval' => (bool) $get('ai_require_human_approval', true),
                'ai_provider' => $get('ai_provider', 'Chat gpt'),
                'ai_model' => $get('ai_model', 'chatgpt-4'),
                'latest_update' => $get('ai_last_updated', now()->format('M j, Y')),
            ],
            'notifications' => [
                'new_notifications' => (bool) $get('notify_new', true),
                'ticket_created' => (bool) $get('notify_ticket', true),
                'invoice_unpaid' => (bool) $get('notify_invoice', false),
            ],
        ];
    }
}
