<?php

namespace App\Http\Resources\API\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketDetailResource extends JsonResource
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
            'ticket_id' => $this->ticket_number,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'avatar' => $this->customer_avatar,
                'location' => 'San Francisco, CA', // Mock
                'customer_since' => 'Jan 2023', // Mock
                'total_orders' => 18, // Mock
                'total_value' => '$2,341', // Mock
                'prev_tickets' => 3, // Mock
            ],
            'subject' => $this->subject,
            'body' => $this->body,
            'category' => $this->category,
            'source' => $this->source,
            'confidence' => (int) $this->confidence,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned' => $this->assigned,
            'updated' => $this->updated_at->diffForHumans(),
            'sla' => '2h', // Mock
            'ai_analysis' => [
                'category' => 'Order Status',
                'risk' => 'LOW',
                'tags' => ['Shipping Delay', 'Order Inquiry', 'High Risk'],
                'reason' => 'Order #SH-8821 was processed on Tuesday. It has been picked and packed. Shopify API confirms tracking is generated but carrier has not yet scanned the parcel.',
                'sources' => ['[API] Shopify', '[Policy] Shipping'],
                'summary' => 'Customer is inquiring about delayed shipment. AI has high confidence in automated resolution. No refund or cancellation risk detected.',
                'suggested_reply' => 'Hi Emma! Your order #SH-8821 will be delivered by FedEx. Based on today\'s pickup, you should expect delivery within 1-2 business days. You can track your package using the link we\'ll send to your email. Is there anything else I can help you with?'
            ],
            'orders' => [
                [
                    'order_id' => '#SH-8821',
                    'status' => 'Ready to Ship',
                    'items' => 2,
                    'total' => '$89.99',
                    'tracking_number' => 'FX-9928374620',
                    'courier' => 'FedEx',
                    'ship_to' => '123 Oak Street, San Francisco, CA 94102'
                ]
            ],
            'messages' => [
                [
                    'sender' => $this->customer_name ?? 'Sarah',
                    'avatar' => substr($this->customer_name ?? 'S', 0, 1),
                    'time' => '2:14 PM',
                    'body' => 'Hi, I placed an order 3 days ago (Order #SH-8821) and it still hasn\'t shipped. Can you tell me what\'s happening?',
                    'is_ai' => false
                ],
                [
                    'sender' => 'Tixolve AI',
                    'avatar' => 'AI',
                    'time' => '2:14 PM',
                    'body' => 'I\'ve looked into your order #SH-8821. It appears there was a small inventory delay on one of the items. Your order is now packed and will be picked up by our courier today. You\'ll receive a tracking email shortly.',
                    'is_ai' => true
                ],
                [
                    'sender' => $this->customer_name ?? 'Sarah',
                    'avatar' => substr($this->customer_name ?? 'S', 0, 1),
                    'time' => '2:16 PM',
                    'body' => 'Oh okay, thank you. Can you also tell me which courier will be delivering it? I want to make sure someone is home.',
                    'is_ai' => false
                ]
            ]
        ];
    }
}
