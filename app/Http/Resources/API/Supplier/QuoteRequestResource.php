<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Helpers\Helper;

/**
 * @mixin \App\Models\QuoteRequest
 */
class QuoteRequestResource extends JsonResource
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
            'location' => [
                'origin' => $this->pickup_address,
                'destination' => $this->delivery_address,
            ],
            'pickup_date' => ! empty($this->pickup_date) ? 'Pickup: '.\Carbon\Carbon::parse($this->pickup_date)->format('j M Y') : '',
            'delivery_date' => ! empty($this->delivery_date) ? 'Delivery: '.\Carbon\Carbon::parse($this->delivery_date)->format('j M Y') : '',
            'status' => ucfirst($this->getSupplierStatus(auth()->id())),
            'client_name' => $this->user?->name ?? 'Unknown',
            'items_summary' => $this->getItemsSummary().', '.number_format($this->items()->sum('weight'), 0).' kg',
            'pallet_type' => $this->getPalletType(),
            'time_ago' => 'receive '.($this->created_at?->diffForHumans() ?? 'recently'),
            'supplier_note' => $this->quotes()->where('user_id', auth()->id())->first()?->notes ?? '',
            'additional_notes' => $this->additional_notes,
            'attachment_url' => Helper::generateURL($this->attachment_path),
            'requested_date' => $this->requested_date ? $this->requested_date->format('j M Y') : 'N/A',
        ];
    }

    /**
     * Generate a summary like "10 Pallets"
     */
    private function getItemsSummary(): string
    {
        $count = $this->items()->sum('quantity');
        $firstItem = $this->items()->first();
        $type = $firstItem ? $firstItem->item_type : 'Items';

        return "{$count} {$type}";
    }
}
