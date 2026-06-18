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
                    'change' => $this['stats']['total_tickets_change'],
                    'trend' => $this['stats']['total_tickets_change'] >= 0 ? 'up' : 'down',
                    'label' => 'from last 7 days'
                ],
                'ai_resolved' => [
                    'value' => number_format($this['stats']['ai_resolved']),
                    'change' => $this['stats']['ai_resolved_change'],
                    'percentage' => $this['stats']['success_rate'],
                    'label' => 'of total tickets'
                ],
                'waiting_approval' => [
                    'value' => $this['stats']['waiting_approval'],
                    'change' => $this['stats']['waiting_approval_change'],
                    'trend' => $this['stats']['waiting_approval_change'] >= 0 ? 'up' : 'down',
                    'status' => $this['stats']['waiting_approval'] > 0 ? 'Needs attention' : 'All clear'
                ],
                'success_rate' => [
                    'value' => number_format($this['stats']['success_rate']) . '%',
                    'change' => $this['stats']['success_rate_change'],
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
