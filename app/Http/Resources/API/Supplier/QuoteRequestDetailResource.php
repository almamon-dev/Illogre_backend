<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Helpers\Helper;

/**
 * @mixin \App\Models\QuoteRequest
 */
class QuoteRequestDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $supplierQuote = $this->quotes()->where('user_id', auth()->id())->first();

        $statusLabel = 'Awaiting client response';
        if ($supplierQuote && $supplierQuote->status === 'accepted') {
            $statusLabel = 'Accepted by client';
        } elseif ($supplierQuote && $supplierQuote->status === 'rejected') {
            $statusLabel = 'Rejected by client';
        }

        return [
            'id' => $this->id,
            'quote_details' => [
                'origin' => $this->pickup_address,
                'destination' => $this->delivery_address,
                'distance_miles' => $this->distance_miles,
                'items_summary' => $this->getItemsSummary(),
                'total_weight' => number_format($this->items()->sum(\DB::raw('weight * quantity')), 0).' kg',
                'dimensions_summary' => $this->getDimensionsSummary(),
                'pallet_type' => $this->getPalletType(),
                'client_name' => $this->user?->name ?? 'Unknown',
                'pickup_date' => ! empty($this->pickup_date) ? \Carbon\Carbon::parse($this->pickup_date)->format('j M Y') : '',
                'delivery_date' => ! empty($this->delivery_date) ? \Carbon\Carbon::parse($this->delivery_date)->format('j M Y') : '',
                'requested_date' => $this->requested_date ? $this->requested_date->format('j M Y') : '',
                'received_at_human' => 'Receive '.($this->created_at?->diffForHumans() ?? 'recently'),
                'additional_notes' => $this->additional_notes,
                'attachment_url' => Helper::generateURL($this->attachment_path),
            ],
            'quote_submitted' => $supplierQuote ? [
                'status_label' => $statusLabel,
                'submitted_price' => '€'.number_format($supplierQuote->amount, 0),
                'estimated_time' => $supplierQuote->estimated_time ?? '2-3 days',
                'notes' => $supplierQuote->notes ?? '',
            ] : [],
        ];
    }

    private function getItemsSummary(): string
    {
        $count = $this->items()->sum('quantity');
        $firstItem = $this->items()->first();
        $type = $firstItem ? $firstItem->item_type : 'Items';

        return "{$count} {$type}";
    }

    private function getDimensionsSummary(): string
    {
        $uniqueDimensions = $this->items()
            ->select('length', 'width', 'height')
            ->distinct()
            ->get();

        if ($uniqueDimensions->isEmpty() || ! $uniqueDimensions->first()->length) {
            return '';
        }

        if ($uniqueDimensions->count() === 1) {
            $dim = $uniqueDimensions->first();

            return "{$dim->length} × {$dim->width} × {$dim->height} cm";
        }

        return $uniqueDimensions->map(function ($dim) {
            return (float) $dim->length.' × '.(float) $dim->width.' × '.(float) $dim->height;
        })->implode(', ').' cm';
    }
}
