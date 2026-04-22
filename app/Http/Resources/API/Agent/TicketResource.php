<?php

namespace App\Http\Resources\API\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ticket_id' => $this->ticket_number,
            'customer' => [
                'name' => $this->customer_name,
                'avatar' => $this->customer_avatar,
            ],
            'subject' => $this->subject,
            'category' => $this->category,
            'source' => $this->source,
            'confidence' => (int) $this->confidence,
            'status' => $this->status,
            'assigned' => $this->assigned,
            'updated' => $this->updated_at->diffForHumans(),
        ];
    }
}
