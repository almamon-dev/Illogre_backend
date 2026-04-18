<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order->order_number,
            'client_name' => $this->order->customer->name,
            'location' => $this->order->pickup_address.' → '.$this->order->delivery_address,
            'completion_date' => $this->order->status === 'completed' ? ($this->order->updated_at ? $this->order->updated_at->format('d M Y') : $this->created_at->format('d M Y')) : '--',
            'price' => '€'.number_format($this->supplier_amount, 0),
            'status' => $this->status === 'paid' ? 'Released' : ucfirst($this->status),
            'payout_date' => $this->paid_at ? $this->paid_at->format('d M Y') : '--',
        ];
    }
}
