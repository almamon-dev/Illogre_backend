<?php

namespace App\Services\Owner;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Calculate Changes (Last 7 days vs Previous 7 days)
        $currTickets = Ticket::where('owner_id', $ownerId)->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $prevTickets = Ticket::where('owner_id', $ownerId)->whereBetween('created_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])->count();
        $ticketsChange = $prevTickets > 0 ? round((($currTickets - $prevTickets) / $prevTickets) * 100, 1) : ($currTickets > 0 ? 100 : 0);

        $currAi = Ticket::where('owner_id', $ownerId)->where('status', 'Resolved')->where('assigned', 'AI Agent')->where('updated_at', '>=', Carbon::now()->subDays(7))->count();
        $prevAi = Ticket::where('owner_id', $ownerId)->where('status', 'Resolved')->where('assigned', 'AI Agent')->whereBetween('updated_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])->count();
        $aiChange = $prevAi > 0 ? round((($currAi - $prevAi) / $prevAi) * 100, 1) : ($currAi > 0 ? 100 : 0);

        $currWaiting = Ticket::where('owner_id', $ownerId)->where('status', 'Pending')->where('updated_at', '>=', Carbon::now()->subDays(7))->count();
        $prevWaiting = Ticket::where('owner_id', $ownerId)->where('status', 'Pending')->whereBetween('updated_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])->count();
        $waitingChange = $prevWaiting > 0 ? round((($currWaiting - $prevWaiting) / $prevWaiting) * 100, 1) : ($currWaiting > 0 ? 100 : 0);

        $currSuccessRate = $currTickets > 0 ? round(($currAi / $currTickets) * 100, 1) : 0;
        $prevSuccessRate = $prevTickets > 0 ? round(($prevAi / $prevTickets) * 100, 1) : 0;
        $successRateChange = $currSuccessRate - $prevSuccessRate;

        // Dynamic Charts
        $ticketVolume = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Ticket::where('owner_id', $ownerId)
                           ->whereDate('created_at', $date->toDateString())
                           ->count();
            $ticketVolume[] = [
                'day' => $date->format('D'),
                'value' => $count
            ];
        }

        $aiResolutionRate = [];
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

            $total = Ticket::where('owner_id', $ownerId)
                           ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                           ->count();
            
            $resolved = Ticket::where('owner_id', $ownerId)
                              ->where('status', 'Resolved')
                              ->where('assigned', 'AI Agent')
                              ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                              ->count();

            $aiResolutionRate[] = [
                'week' => 'Week ' . (4 - $i),
                'resolved' => $resolved,
                'total' => $total,
            ];
        }

        return [
            'stats' => [
                'total_tickets' => $totalTickets,
                'total_tickets_change' => $ticketsChange,
                'ai_resolved' => $aiResolved,
                'ai_resolved_change' => $aiChange,
                'waiting_approval' => $waitingApproval,
                'waiting_approval_change' => $waitingChange,
                'success_rate' => $totalTickets > 0 ? round(($aiResolved / $totalTickets) * 100, 1) : 0,
                'success_rate_change' => $successRateChange,
                'time_saved' => $aiResolved * 0.5, // Mock: 30 mins per AI resolved ticket
            ],
            'charts' => [
                'ticket_volume' => $ticketVolume,
                'ai_resolution_rate' => $aiResolutionRate,
            ],
            'recent_tickets' => $recentTickets,
        ];
    }
}
