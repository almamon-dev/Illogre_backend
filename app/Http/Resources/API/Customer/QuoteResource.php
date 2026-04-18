<?php

namespace App\Http\Resources\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
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
            'amount' => '€'.number_format($this->amount, 0),
            'supplier_name' => $this->supplier?->company_name ?? $this->supplier?->name ?? 'Supplier',
            'rating' => round(\App\Models\Review::where('supplier_id', $this->user_id)->avg('rating') ?? 5.0, 1),
            'completed_orders' => (\App\Models\Order::where('supplier_id', $this->user_id)->where('status', 'completed')->count() ?? 0) . " completed orders",
            'available_capacity' => 'Available Capacity : 15 Pallets',
            'notes' => $this->notes ?? '',
            'client_notes' => $this->quoteRequest?->additional_notes ?? '',
            'pallet_type' => $this->quoteRequest?->getPalletType() ?? 'N/A',
            'pickup_date' => $this->quoteRequest?->pickup_date ? \Carbon\Carbon::parse($this->quoteRequest?->pickup_date)->format('j M Y') : '',
            'delivery_date' => $this->quoteRequest?->delivery_date ? \Carbon\Carbon::parse($this->quoteRequest?->delivery_date)->format('j M Y') : '',
            'estimated_delivery' => $this->estimated_time ?? '2-3 days',
        ];
    }
}
