<?php

namespace App\Http\Resources\API\Supplier;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $statusTimeline = [
            'pending' => 0, 'confirmed' => 1, 'in_progress' => 2,
            'picked_up' => 3, 'delivered' => 4, 'completed' => 5,
        ];

        $nextActions = [
            'pending' => ['text' => 'Confirm Order', 'target' => 'confirmed'],
            'confirmed' => ['text' => 'Mark as in Progress', 'target' => 'in_progress'],
            'in_progress' => ['text' => 'Mark as Picked Up', 'target' => 'picked_up'],
            'picked_up' => ['text' => 'Mark as Delivered', 'target' => 'delivered'],
            'delivered' => ['text' => 'Mark as Completed', 'target' => 'completed'],
        ];

        return [
            'id' => $this->id,
            'order_no' => $this->order_number,
            'status' => $this->status,

            'client' => [
                'name' => $this->customer?->name,
                'avatar' => $this->customer?->profile_picture,
            ],

            'payment' => [
                'total' => (float) $this->total_amount,
                'formatted' => '€'.number_format($this->total_amount, 2),
                'is_paid' => $this->invoice?->status === 'paid',
            ],

            'shipping' => [
                'service' => $this->pallet_type,
                'route' => $this->getLocationSummary(),
                'pickup_at' => $this->pickup_date ? Carbon::parse($this->pickup_date)->format('d M Y') : null,
                'from' => $this->pickup_address,
                'to' => $this->delivery_address,
                'instructions' => $this->quote?->quoteRequest?->additional_notes,
            ],

            'tracking' => [
                'current_step' => $statusTimeline[$this->status] ?? 0,
                'steps' => ['Pending', 'Confirmed', 'In Progress', 'Picked Up', 'Delivered', 'Completed'],
                'note' => $this->status_note,
                'proof' => Helper::generateURL($this->proof_of_delivery),
                'history' => $this->updates->map(fn ($update) => [
                    'status' => $update->status,
                    'title' => $update->title,
                    'description' => $update->description,
                    'date' => $update->created_at->format('d M Y, h:i A'),
                ]),
            ],

            'next_step' => $nextActions[$this->status] ?? null,

            'shipment' => [
                'items_count' => $this->items()->count(),
                'total_weight' => $this->items()->sum('weight') > 0 ? $this->items()->sum('weight').' kg' : 'N/A',
                'dimensions' => ($firstItem = $this->items()->first()) ? "{$firstItem->length} × {$firstItem->width} × {$firstItem->height} cm" : 'N/A',
                'description' => $this->getItemsSummary(),
            ],

            'date' => $this->created_at?->format('d M Y, h:i A'),
        ];
    }

    private function getItemsSummary(): string
    {
        $items = $this->items;
        if (! $items || $items->isEmpty()) {
            return '0 Items';
        }

        $count = $items->sum('quantity');
        $type = $items->first()->item_type ?? 'Items';

        return "{$count} {$type}";
    }

    private function getLocationSummary()
    {
        $pickup = explode(',', $this->pickup_address);
        $delivery = explode(',', $this->delivery_address);

        return trim(end($pickup)).' to '.trim(end($delivery));
    }
}
