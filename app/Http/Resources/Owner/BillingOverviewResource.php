<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'usage_overview' => [
                'tickets_used' => [
                    'current' => $this['usage']['tickets_used'],
                    'total' => $this['usage']['tickets_total'],
                    'percentage' => $this['usage']['tickets_total'] > 0 ? round(($this['usage']['tickets_used'] / $this['usage']['tickets_total']) * 100) : 0,
                ],
                'team_members' => [
                    'current' => $this['usage']['team_members'],
                    'total' => $this['usage']['team_total'],
                    'percentage' => $this['usage']['team_total'] > 0 ? round(($this['usage']['team_members'] / $this['usage']['team_total']) * 100) : 0,
                ],
                'ai_actions' => [
                    'current' => $this['usage']['ai_actions'],
                    'total' => $this['usage']['ai_total'],
                    'percentage' => $this['usage']['ai_total'] > 0 ? round(($this['usage']['ai_actions'] / $this['usage']['ai_total']) * 100) : 0,
                ],

            ],
            'current_plan' => $this['plan']['is_active'] ? [
                'name' => $this['plan']['name'],
                'price' => $this['plan']['price'],
                'description' => $this['plan']['description'],
            ] : [],
            'billing_history' => $this['history'],
        ];
    }
}
