<?php

namespace App\Http\Resources\API\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['customer']->id,
            'name' => $this['customer']->name,
            'email' => $this['customer']->email,
            'phone' => $this['customer']->phone,
            'country' => $this['customer']->country,
            'status' => $this['customer']->status,
            'ltv' => '$' . number_format($this['customer']->total_spent, 2),
            'total_orders' => $this['customer']->total_orders,
            'tickets' => [
                'open' => $this['tickets']['open'],
                'closed' => $this['tickets']['closed'],
            ],
            'order_history' => $this['order_history'],
        ];
    }
}
