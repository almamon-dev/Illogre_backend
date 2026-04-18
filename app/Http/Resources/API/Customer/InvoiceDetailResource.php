<?php

namespace App\Http\Resources\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->order;
        $items = $order?->items;
        $firstItem = $items?->first();

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),

            'order_info' => [
                'order_number' => $order?->order_number,
                'pallet_type' => $order?->getPalletType() ?? 'Road Freight/ Pallet Transport',
                'supplier_name' => $order?->supplier?->name ?? 'Swift Transport Co.',
            ],

            'items_summary' => [
                'description' => $this->getItemsSummary($items),
                'total_weight' => 'Total weight : '.number_format($items?->sum(fn ($i) => $i->weight * $i->quantity) ?? 0, 0).' kg',
                'dimensions' => ($firstItem ? "{$firstItem->length} × {$firstItem->width} × {$firstItem->height} cm" : 'N/A'),
            ],

            'delivery_info' => [
                'delivery_date' => $order?->pickup_date ? \Carbon\Carbon::parse($order->pickup_date)->addDays(3)->format('j M Y') : '20 Jan 2026',
                'total_amount' => number_format($order?->total_amount ?? 0, 0),
            ],

            'addresses' => [
                'pickup' => $order?->pickup_address ?? 'New York, NY',
                'delivery' => $order?->delivery_address ?? 'Boston, MA',
                'distance' => '210 Miles',
            ],

            'pod_status' => [
                'status' => $order?->pod_status ?? 'awaiting',
                'label' => 'POD ('.ucfirst($order?->pod_status ?? 'awaiting').')',
            ],

            'amount_breakdown' => [
                'supplier_amount' => '€'.number_format($this->supplier_amount, 0),
                'platform_fee' => '€'.number_format($this->platform_fee, 0),
                'total_payable' => '€'.number_format($this->total_amount, 0),
            ],

            'payment_details' => [
                'invoice_date' => $this->created_at?->format('j F Y'),
                'due_date' => $this->due_date ? \Carbon\Carbon::parse($this->due_date)->format('j F Y') : 'N/A',
                'payment_terms' => 'Net 30',
            ],
        ];
    }

    private function getItemsSummary($items): string
    {
        if (! $items || $items->isEmpty()) {
            return '0 Items';
        }

        $count = $items->sum('quantity');
        $type = $items->first()->item_type ?? 'Items';

        return "{$count} {$type}";
    }
}
