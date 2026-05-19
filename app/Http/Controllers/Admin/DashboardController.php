<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->user_type === 'super_admin') {
            // 1. Original Stats (Mock for Super Admin)
            $stats = [
                'total_tickets' => [
                    'value' => '1,234',
                    'trend' => '+12.5%',
                    'trend_label' => 'from last week',
                    'type' => 'up',
                ],
                'ai_resolved' => [
                    'value' => '892',
                    'trend' => '+12.5%',
                    'trend_label' => '75.0% of total',
                    'type' => 'up',
                ],
                'waiting_approval' => [
                    'value' => '156',
                    'trend' => '-15.4%',
                    'trend_label' => 'Needs attention',
                    'type' => 'down',
                ],
                'success_rate' => [
                    'value' => '1,234',
                    'trend' => '+12.5%',
                    'trend_label' => 'Automation rate',
                    'type' => 'up',
                ],
                'time_saved' => [
                    'value' => '42h',
                    'trend' => null,
                    'trend_label' => 'AI tickets × avg handling time',
                    'type' => 'neutral',
                ],
            ];

            // 2. Ticket Volume Chart
            $ticketVolume = [
                ['day' => 'Mon', 'tickets' => 200],
                ['day' => 'Tue', 'tickets' => 320],
                ['day' => 'Wed', 'tickets' => 400],
                ['day' => 'Thu', 'tickets' => 310],
                ['day' => 'Fri', 'tickets' => 325],
                ['day' => 'Sat', 'tickets' => 210],
                ['day' => 'Sun', 'tickets' => 380],
            ];

            // 3. AI Resolution Rate Chart
            $resolutionRate = [
                ['name' => 'Week 1', 'resolved' => 380, 'total' => 600],
                ['name' => 'Week 2', 'resolved' => 150, 'total' => 350],
                ['name' => 'Week 3', 'resolved' => 450, 'total' => 600],
                ['name' => 'Week 4', 'resolved' => 140, 'total' => 380],
                ['name' => 'Week 5', 'resolved' => 120, 'total' => 180],
            ];

            // 4. Recent Tickets Table
            $recentTickets = [
                [
                    'id' => 'ORD-10024',
                    'customer' => ['name' => 'Sarah', 'avatar' => null],
                    'subject' => ['title' => 'Where is my order?', 'subtitle' => 'Order Status'],
                    'source' => 'Chat',
                    'confidence' => 82,
                    'status' => 'Resolved',
                    'assigned' => 'AI Agent',
                    'updated' => '10 min ago',
                ],
            ];
        } else {
            // Load real data for the Owner/Member's workspace
            $ownerId = $user->getTeamOwnerId();

            $totalTickets = Ticket::where('owner_id', $ownerId)->count();
            $aiResolved = Ticket::where('owner_id', $ownerId)->where('status', 'Resolved')->where('assigned', 'AI Agent')->count();
            $waitingApproval = Ticket::where('owner_id', $ownerId)->where('status', 'Pending')->count();
            $successRate = $totalTickets > 0 ? round(($aiResolved / $totalTickets) * 100, 1) : 0;
            $timeSaved = $aiResolved * 0.5; // Average 30 mins saved per AI resolved ticket

            $stats = [
                'total_tickets' => [
                    'value' => number_format($totalTickets),
                    'trend' => $totalTickets > 0 ? '+10%' : null,
                    'trend_label' => 'from last week',
                    'type' => 'up',
                ],
                'ai_resolved' => [
                    'value' => number_format($aiResolved),
                    'trend' => null,
                    'trend_label' => 'AI-resolved tickets',
                    'type' => 'up',
                ],
                'waiting_approval' => [
                    'value' => number_format($waitingApproval),
                    'trend' => null,
                    'trend_label' => $waitingApproval > 0 ? 'Needs attention' : 'All caught up',
                    'type' => 'down',
                ],
                'success_rate' => [
                    'value' => $successRate . '%',
                    'trend' => null,
                    'trend_label' => 'Automation rate',
                    'type' => 'up',
                ],
                'time_saved' => [
                    'value' => $timeSaved . 'h',
                    'trend' => null,
                    'trend_label' => 'Hours saved by AI',
                    'type' => 'neutral',
                ],
            ];

            // Real weekly or daily volume chart (last 7 days)
            $ticketVolume = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dayName = $date->format('D');
                $count = Ticket::where('owner_id', $ownerId)
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
                $ticketVolume[] = ['day' => $dayName, 'tickets' => $count];
            }

            // Real resolution rate chart
            $resolutionRate = [
                ['name' => 'Active', 'resolved' => $aiResolved, 'total' => $totalTickets],
            ];

            // Real recent tickets
            $recentTickets = Ticket::where('owner_id', $ownerId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_number,
                        'customer' => [
                            'name' => $ticket->customer_name,
                            'avatar' => $ticket->customer_avatar
                        ],
                        'subject' => [
                            'title' => $ticket->subject,
                            'subtitle' => $ticket->category ?? 'Inquiry'
                        ],
                        'source' => $ticket->source,
                        'confidence' => $ticket->confidence,
                        'status' => $ticket->status,
                        'assigned' => $ticket->assigned,
                        'updated' => $ticket->updated_at->diffForHumans(),
                    ];
                })
                ->toArray();
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ticket_volume' => $ticketVolume,
            'resolution_rate' => $resolutionRate,
            'recent_tickets' => $recentTickets,
        ]);
    }
}
