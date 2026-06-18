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
        $orders = [];
        $customerStats = [
            'location' => 'Unknown',
            'customer_since' => 'N/A',
            'total_orders' => 0,
            'total_value' => '$0.00',
            'prev_tickets' => 0,
        ];

        if ($this->customer_id) {
            $customer = \App\Models\Customer::find($this->customer_id);
            if ($customer) {
                $totalOrdersFromDb = \App\Models\Order::where('customer_id', $this->customer_id)->count();
                $totalSpentFromDb = \App\Models\Order::where('customer_id', $this->customer_id)->sum('total_price');

                $customerStats['total_orders'] = $totalOrdersFromDb > 0 ? $totalOrdersFromDb : ($customer->total_orders ?? 0);
                $customerStats['total_value'] = '$' . number_format($totalSpentFromDb > 0 ? $totalSpentFromDb : ($customer->total_spent ?? 0), 2);
                $location = $customer->country;
                if (empty($location)) {
                    $latestOrder = \App\Models\Order::where('customer_id', $this->customer_id)->latest()->first();
                    if ($latestOrder) {
                        $raw = is_string($latestOrder->raw_data) ? json_decode($latestOrder->raw_data, true) : ($latestOrder->raw_data ?? []);
                        $location = $raw['customer']['default_address']['country'] ?? 'Unknown';
                        if ($location !== 'Unknown') {
                            $customer->update(['country' => $location]);
                        }
                    } else {
                        $location = 'Unknown';
                    }
                }
                $customerStats['location'] = $location;
                $customerStats['customer_since'] = $customer->created_at ? $customer->created_at->format('M Y') : 'N/A';
                $customerStats['prev_tickets'] = \App\Models\Ticket::where('customer_id', $this->customer_id)->count();

                $dbOrders = \App\Models\Order::where('customer_id', $this->customer_id)
                    ->latest('shopify_created_at')
                    ->limit(5)
                    ->get();

                foreach ($dbOrders as $order) {
                    $raw = is_string($order->raw_data) ? json_decode($order->raw_data, true) : ($order->raw_data ?? []);
                    
                    $tracking = 'N/A';
                    $courier = 'N/A';
                    if (!empty($raw['fulfillments']) && is_array($raw['fulfillments'])) {
                        $tracking = $raw['fulfillments'][0]['tracking_number'] ?? 'N/A';
                        $courier = $raw['fulfillments'][0]['tracking_company'] ?? 'N/A';
                    }

                    $shipTo = 'N/A';
                    if (!empty($raw['shipping_address'])) {
                        $shipTo = ($raw['shipping_address']['address1'] ?? '') . ', ' . ($raw['shipping_address']['city'] ?? '');
                        $shipTo = trim($shipTo, ', ');
                    }

                    $orders[] = [
                        'order_id' => $order->order_number ?? '#' . $order->shopify_order_id,
                        'status' => $order->fulfillment_status ? ucfirst($order->fulfillment_status) : 'Unfulfilled',
                        'items' => !empty($raw['line_items']) ? count($raw['line_items']) : 0,
                        'total' => '$' . number_format((float)$order->total_price, 2),
                        'tracking_number' => $tracking,
                        'courier' => $courier,
                        'ship_to' => $shipTo ?: 'N/A',
                    ];
                }
            }
        }

        $aiData = $this->ai_analysis ?? [];

        $dbMessages = $this->messages()->oldest()->get()->map(function($msg) {
            return [
                'id' => $msg->id,
                'sender' => $msg->sender_name,
                'body' => $msg->body,
                'time' => $msg->created_at->format('h:i A'),
                'is_ai' => (bool)$msg->is_ai,
                'is_internal' => (bool)$msg->is_internal,
            ];
        })->toArray();

        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_number,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'avatar' => $this->customer_avatar,
                'location' => $customerStats['location'],
                'customer_since' => $customerStats['customer_since'],
                'total_orders' => $customerStats['total_orders'],
                'total_value' => $customerStats['total_value'],
                'prev_tickets' => $customerStats['prev_tickets'],
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
            'sla' => '2h',
            'ai_analysis' => [
                'category' => $aiData['category'] ?? ($this->category ?? 'General Inquiry'),
                'risk' => $this->confidence < 50 ? 'HIGH' : ($this->confidence < 80 ? 'MEDIUM' : 'LOW'),
                'tags' => $aiData['tags'] ?? ['Support', $this->category ?? 'Inquiry', $this->source ?? 'Email'],
                'reason' => $aiData['reason'] ?? 'Based on the ticket content: "' . strip_tags($this->body) . '", the system has classified this as a ' . ($this->category ?? 'General Inquiry') . '.',
                'sources' => $aiData['sources'] ?? ['[System] Tixolve AI', '[Data] Customer History'],
                'summary' => $aiData['summary'] ?? 'Customer is inquiring about "' . $this->subject . '". AI confidence is at ' . $this->confidence . '%.',
                'suggested_reply' => $aiData['suggested_reply'] ?? ($this->ai_suggested_reply ?? "Hi " . strtok($this->customer_name ?? 'there', ' ') . ",\n\nThank you for reaching out regarding " . strtolower($this->subject) . ". We have received your inquiry and our team is currently reviewing it. We will get back to you with an update shortly.\n\nBest regards,\nSupport Team")
            ],
            'orders' => $orders,
            'messages' => $dbMessages
        ];
    }
}
