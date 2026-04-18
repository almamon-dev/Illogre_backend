<?php

namespace App\Http\Resources\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Helpers\Helper;

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
            'pickup_address' => $this->pickup_address,
            'delivery_address' => $this->delivery_address,
            'status' => $this->status,
            'type_of_pallets' => $this->getPalletType(),
            'pickup_date' => $this->pickup_date ? \Carbon\Carbon::parse($this->pickup_date)->format('j M Y') : '',
            'delivery_date' => $this->delivery_date ? \Carbon\Carbon::parse($this->delivery_date)->format('j M Y') : '',
            'created_at' => $this->created_at,
            'additional_notes' => $this->additional_notes,
            'attachment_url' => Helper::generateURL($this->attachment_path),
            'supplier_note' => $this->quotes()->first()?->notes ?? '',
            'quotes_count' => $this->quotes_count ?? $this->quotes()->count(),
        ];
    }
}
