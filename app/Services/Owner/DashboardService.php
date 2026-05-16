<?php

namespace App\Services\Owner;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getOverviewData(): array
    {
        $ownerId = Auth::user()->getTeamOwnerId();

        // 1. Fetch Real Stats
        $totalTickets = Ticket::where('owner_id', $ownerId)->count();
        $aiResolved = Ticket::where('owner_id', $ownerId)->where('status', 'Resolved')->where('assigned', 'AI Agent')->count();
        $waitingApproval = Ticket::where('owner_id', $ownerId)->where('status', 'Pending')->count();

        // 2. Fetch Recent Tickets with Customer and Orders
        $recentTickets = Ticket::where('owner_id', $ownerId)
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($ticket) use ($ownerId) {
                // Fetch recent orders for this customer to show in UI context
                $orders = [];
                if ($ticket->customer_id) {
                    $orders = Order::where('customer_id', $ticket->customer_id)
                        ->where('owner_id', $ownerId)
                        ->orderBy('shopify_created_at', 'desc')
                        ->limit(2)
                        ->get()
                        ->map(function($order) {
                            return [
                                'order_number' => $order->order_number,
                                'total' => $order->total_price . ' ' . $order->currency,
                                'status' => $order->financial_status,
                                'fulfillment' => $order->fulfillment_status,
                            ];
                        });
                }

                return [
                    'ticket_id' => $ticket->ticket_number,
                    'customer' => [
                        'name' => $ticket->customer_name,
                        'email' => $ticket->customer_email,
                        'avatar' => $ticket->customer_avatar,
                    ],
                    'subject' => $ticket->subject,
                    'source' => $ticket->source,
                    'confidence' => $ticket->confidence,
                    'status' => $ticket->status,
                    'assigned' => $ticket->assigned,
                    'updated' => $ticket->updated_at->diffForHumans(),
                    'recent_orders' => $orders, // This is the new part!
                ];
            });

        return [
            'stats' => [
                'total_tickets' => $totalTickets,
                'ai_resolved' => $aiResolved,
                'waiting_approval' => $waitingApproval,
                'success_rate' => $totalTickets > 0 ? round(($aiResolved / $totalTickets) * 100, 1) : 0,
                'time_saved' => $aiResolved * 0.5, // Mock: 30 mins per AI resolved ticket
            ],
            'charts' => [
                'ticket_volume' => [
                    ['day' => 'Mon', 'value' => 210],
                    ['day' => 'Tue', 'value' => 280],
                    ['day' => 'Wed', 'value' => 400],
                    ['day' => 'Thu', 'value' => 320],
                    ['day' => 'Fri', 'value' => 250],
                    ['day' => 'Sat', 'value' => 380],
                    ['day' => 'Sun', 'value' => 450],
                ],
                'ai_resolution_rate' => [
                    ['week' => 'Week 1', 'resolved' => 380, 'total' => 600],
                    ['week' => 'Week 2', 'resolved' => 250, 'total' => 500],
                    ['week' => 'Week 3', 'resolved' => 450, 'total' => 600],
                    ['week' => 'Week 4', 'resolved' => 280, 'total' => 450],
                ],
            ],
            'recent_tickets' => $recentTickets,
        ];
    }
}
