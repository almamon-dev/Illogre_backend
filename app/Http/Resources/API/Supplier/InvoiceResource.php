<?php

namespace App\Http\Resources\API\Supplier;

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
            'amount' => (float) $this->supplier_amount,
            'amount_formatted' => '€' . number_format($this->supplier_amount, 2),
            'status' => $this->status,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
