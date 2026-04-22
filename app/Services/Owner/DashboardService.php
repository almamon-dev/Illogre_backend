<?php

namespace App\Services\Owner;

class DashboardService
{
    public function getOverviewData(): array
    {

        return [
            'stats' => [
                'total_tickets' => 1234,
                'ai_resolved' => 892,
                'waiting_approval' => 156,
                'success_rate' => 1234,
                'time_saved' => 42,
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
            'recent_tickets' => [
                [
                    'ticket_id' => 'ORD-10024',
                    'customer' => ['name' => 'Sarah', 'avatar' => null],
                    'subject' => 'Where is my order?',
                    'source' => 'Chat',
                    'confidence' => 82,
                    'status' => 'Resolved',
                    'assigned' => 'AI Agent',
                    'updated' => '10 min ago',
                ],
                [
                    'ticket_id' => 'ORD-10025',
                    'customer' => ['name' => 'Sarah', 'avatar' => null],
                    'subject' => 'Where is my order?',
                    'source' => 'Shopify',
                    'confidence' => 95,
                    'status' => 'Resolved',
                    'assigned' => 'AI Agent',
                    'updated' => '10 min ago',
                ],
                [
                    'ticket_id' => 'ORD-10026',
                    'customer' => ['name' => 'Sarah', 'avatar' => null],
                    'subject' => 'Where is my order?',
                    'source' => 'Email',
                    'confidence' => 42,
                    'status' => 'Resolved',
                    'assigned' => 'AI Agent',
                    'updated' => '10 min ago',
                ],
                [
                    'ticket_id' => 'ORD-10027',
                    'customer' => ['name' => 'Sarah', 'avatar' => null],
                    'subject' => 'Where is my order?',
                    'source' => 'Chat',
                    'confidence' => 0,
                    'status' => 'Pending',
                    'assigned' => 'AI Agent',
                    'updated' => '10 min ago',
                ],
                [
                    'ticket_id' => 'ORD-10028',
                    'customer' => ['name' => 'Sarah', 'avatar' => null],
                    'subject' => 'Where is my order?',
                    'source' => 'Resolved',
                    'confidence' => 15,
                    'status' => 'Resolved',
                    'assigned' => 'AI Agent',
                    'updated' => '10 min ago',
                ],
            ],
        ];
    }
}
