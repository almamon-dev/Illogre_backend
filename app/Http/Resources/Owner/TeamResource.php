<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'role' => $this->role ?? 'N/A',
            'tickets' => 0, // Placeholder
            'resolved' => 0, // Placeholder
            'status' => $this->status,
            'is_online' => $this->last_active_at && $this->last_active_at->gt(now()->subMinutes(5)),
            'last_active' => $this->last_active_at ? $this->last_active_at->diffForHumans() : 'Never',
        ];
    }
}
