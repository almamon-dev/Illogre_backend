<?php

namespace App\Http\Resources\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusTimeline = [
            'pending' => 0,
            'confirmed' => 1,
            'in_progress' => 2,
            'picked_up' => 3,
            'delivered' => 4,
            'completed' => 5,
        ];

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => (float) $this->total_amount,
            'total_amount_formatted' => '€'.number_format($this->total_amount, 2),
            'status' => $this->status,
            'status_note' => $this->status_note,
            'pickup_address' => $this->pickup_address,
            'delivery_address' => $this->delivery_address,
            'pickup_date' => $this->pickup_date,
            'estimated_time' => $this->estimated_time,
            'proof_of_delivery' => \App\Helpers\Helper::generateURL($this->proof_of_delivery),
            'pod_status' => $this->pod_status,
            'tracking' => [
                'current_step' => $statusTimeline[$this->status] ?? 0,
                'steps' => ['Pending', 'Confirmed', 'In Progress', 'Picked Up', 'Delivered', 'Completed'],
            ],
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_type' => $item->item_type,
                        'quantity' => $item->quantity,
                        'length' => $item->length,
                        'width' => $item->width,
                        'height' => $item->height,
                        'weight' => $item->weight,
                    ];
                });
            }),
            'review' => $this->review ? [
                'rating' => $this->review->rating,
                'comment' => $this->review->comment,
                'date' => $this->review->created_at->format('d M Y'),
            ] : [],
            'supplier' => [
                'id' => $this->supplier?->id,
                'name' => $this->supplier?->name,
                'company_name' => $this->supplier?->company_name,
                'profile_picture' => \App\Helpers\Helper::generateURL($this->supplier?->profile_picture) ?? null,
            ],
            'live_updates' => $this->updates->map(function ($update) {
                return [
                    'title' => $update->title,
                    'description' => $update->description,
                    'status' => $update->status,
                    'time_ago' => $update->created_at->diffForHumans(),
                    'created_at' => $update->created_at->format('d M Y, h:i A'),
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
