<?php

namespace App\Http\Resources\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'order_number' => $this->order?->order_number,
            'supplier_name' => $this->order?->supplier?->name ?? 'Swift Transport Co.',
            'amount' => '€' . number_format($this->total_amount, 0),
            'due_date' => $this->due_date ? \Carbon\Carbon::parse($this->due_date)->format('j M Y') : 'N/A',
            'status' => ucfirst($this->status),
            'raw_status' => $this->status, // for frontend logic
            'invoice_date' => $this->created_at?->format('j F Y'),
        ];
    }
}
