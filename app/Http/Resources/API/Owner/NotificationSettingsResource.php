<?php

namespace App\Http\Resources\API\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'new_notifications' => (bool)$this->getSetting('notify_new', true),
            'ticket_created' => (bool)$this->getSetting('notify_ticket', true),
            'invoice_unpaid' => (bool)$this->getSetting('notify_invoice', false),
        ];
    }
}
