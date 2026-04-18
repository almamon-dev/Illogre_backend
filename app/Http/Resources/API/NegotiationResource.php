<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NegotiationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = auth()->user();
        $quote = \App\Models\Quote::with(['user', 'quoteRequest.user'])->find($this->data['quote_id'] ?? 0);

        // Determine who is the "other party" based on current user
        if ($currentUser->user_type === 'customer') {
            // Customer sees supplier info
            $otherParty = $quote?->user; // Supplier
            $senderName = $otherParty?->name ?? $this->data['supplier_name'] ?? 'Supplier';
        } else {
            // Supplier sees customer info
            $otherParty = $quote?->quoteRequest?->user; // Customer
            $senderName = $otherParty?->name ?? 'Customer';
        }

        return [
            'id' => $this->id,
            'sender_id' => $otherParty?->id ?? ($this->data['supplier_id'] ?? 0),
            'sender_name' => $senderName,
            'profile_picture' => $otherParty?->profile_picture ?? 'https://ui-avatars.com/api/?name='.urlencode($senderName).'&color=7F9CF5&background=EBF4FF',
            'message_snippet' => $this->data['message'] ?? 'New quote received.',
            'time_ago' => $this->created_at?->diffForHumans(),
            'quote_id' => (int) ($this->data['quote_id'] ?? 0),
            'quote_request_id' => (int) ($this->data['quote_request_id'] ?? 0),
            'amount' => (float) ($this->data['amount'] ?? 0),
            'pallet_type' => $this->data['pallet_type'] ?? null,
            'read_at' => $this->read_at,
            'type' => $this->data['type'] ?? 'new_quote', // 'new_quote' or 'revision'
        ];
    }
}
