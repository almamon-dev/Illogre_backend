<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Original Stats
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
            // More dummy data...
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ticket_volume' => $ticketVolume,
            'resolution_rate' => $resolutionRate,
            'recent_tickets' => $recentTickets,
        ]);
    }
}
