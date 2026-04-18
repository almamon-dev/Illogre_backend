<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'quote_id' => $this->quote_id,
            'message' => $this->message,
            'is_me' => $this->sender_id === auth()->id(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'time' => $this->created_at?->format('H:i'),
            'date' => $this->created_at?->format('j F'),
        ];
    }
}
