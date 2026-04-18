<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stats' => [
                'total_tickets' => [
                    'value' => number_format($this['stats']['total_tickets']),
                    'change' => 12.5,
                    'trend' => 'up',
                    'label' => 'from last week'
                ],
                'ai_resolved' => [
                    'value' => number_format($this['stats']['ai_resolved']),
                    'change' => 12.5,
                    'percentage' => 75.0,
                    'label' => 'of total'
                ],
                'waiting_approval' => [
                    'value' => $this['stats']['waiting_approval'],
                    'change' => -15.4,
                    'trend' => 'down',
                    'status' => 'Needs attention'
                ],
                'success_rate' => [
                    'value' => number_format($this['stats']['success_rate']),
                    'change' => 12.5,
                    'label' => 'Automation rate'
                ],
                'time_saved' => [
                    'value' => $this['stats']['time_saved'] . 'h',
                    'label' => 'AI tickets * avg handling time'
                ]
            ],
            'charts' => [
                'ticket_volume' => $this['charts']['ticket_volume'],
                'ai_resolution_rate' => $this['charts']['ai_resolution_rate']
            ],
            'recent_tickets' => $this['recent_tickets']
        ];
    }
}
