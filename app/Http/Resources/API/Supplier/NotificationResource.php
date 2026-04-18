<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => str_replace('App\\Notifications\\', '', $this->type),
            'data' => $this->data,
            'read_at' => $this->read_at ? $this->read_at->format('d M Y, h:i A') : null,
            'created_at' => $this->created_at->format('d M Y, h:i A'),
            'time_ago' => $this->created_at->diffForHumans(),
        ];
    }
}
