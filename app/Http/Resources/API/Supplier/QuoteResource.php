<?php

namespace App\Http\Resources\API\Supplier;

use App\Http\Resources\API\Supplier\QuoteRequestResource;
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
            'quote_request_id' => (int) $this->quote_request_id,
            'amount' => (float) $this->amount,
            'estimated_time' => $this->estimated_time,
            'notes' => $this->notes ?? '',
            'valid_until' => $this->valid_until,
            'status' => $this->status,
            'revised_amount' => $this->revised_amount ? (float) $this->revised_amount : null,
            'revised_amount_formatted' => $this->revised_amount ? '€' . number_format($this->revised_amount, 0) : null,
            'revised_estimated_time' => $this->revised_estimated_time,
            'revision_status' => $this->revision_status,
            'chat_id' => $this->id, // Using Quote ID as Chat ID
            'supplier' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'company_name' => $this->user->company_name,
                ];
            }),
            'quote_request' => new QuoteRequestResource($this->whenLoaded('quoteRequest')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
