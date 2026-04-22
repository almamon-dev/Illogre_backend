<?php

namespace App\Http\Resources\API\Owner;

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
        $get = fn($key, $default = null) => $this->getSetting($key, $default);

        return [
            'personal_details' => [
                'name' => $this->name,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'avatar_url' => $get('avatar_url'),
            ],
            'company_details' => [
                'company_name' => $this->company_name,
                'country' => $get('country'),
                'vat_id' => $get('vat_id'),
                'address' => $get('address'),
                'brand_logo' => $get('brand_logo'),
            ],

            'ai_settings' => [
                'expert_name' => $get('ai_expert_name'),
                'expert_persona' => $get('ai_expert_persona'),
                'expert_guidance' => $get('ai_expert_guidance'),
            ],
            'notifications' => [
                'new_notifications' => $get('notify_new', true),
                'ticket_created' => $get('notify_ticket', true),
                'invoice_unpaid' => $get('notify_invoice', false),
            ],
        ];
    }
}
