<?php

namespace App\Http\Resources\API\Manager;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
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
            'role' => $this->role,
            'tickets_count' => $this->tickets_count ?? 0,
            'resolved_count' => $this->resolved_tickets_count ?? 0,
            'status' => $this->last_active_at && $this->last_active_at->gt(now()->subMinutes(5)) ? 'Online' : 'Offline',
            'last_active' => $this->last_active_at ? $this->last_active_at->diffForHumans() : 'Never',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
