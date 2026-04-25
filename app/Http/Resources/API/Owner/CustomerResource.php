<?php

namespace App\Http\Resources\API\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'country' => $this->country,
            'orders' => $this->total_orders ?? 0,
            'tickets' => $this->tickets_count ?? 0,
            'value' => '$'.number_format($this->total_spent, 2),
            'status' => $this->status,
            'last_active' => ($this->last_interaction_at ?: $this->created_at)->diffForHumans(),
        ];
    }
}
