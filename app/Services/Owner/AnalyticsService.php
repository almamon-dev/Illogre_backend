<?php

namespace App\Services\Owner;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getAnalyticsData($range = '7days'): array
    {
        $ownerId = Auth::user()->getTeamOwnerId();
        
        $days = $range === '30days' ? 30 : ($range === '90days' ? 90 : 7);
        $startDate = Carbon::now()->subDays($days);

        // Current Period Queries
        $baseQuery = Ticket::where('owner_id', $ownerId)->where('created_at', '>=', $startDate);
        $totalTickets = (clone $baseQuery)->count();
        
        $aiResolvedQuery = (clone $baseQuery)->where('status', 'Resolved')->where('assigned', 'AI Agent');
        $humanResolvedQuery = (clone $baseQuery)->where('status', 'Resolved')->where('assigned', '!=', 'AI Agent');
        
        $aiResolvedCount = $aiResolvedQuery->count();
        $resolvedTodayCount = Ticket::where('owner_id', $ownerId)->whereDate('created_at', Carbon::today())->where('status', 'Resolved')->count();
        
        $automationRate = $totalTickets > 0 ? round(($aiResolvedCount / $totalTickets) * 100, 1) : 0;
        
        // Averages for current period
        $avgAiAccuracy = (clone $baseQuery)->whereNotNull('confidence')->avg('confidence') ?? 0;
        
        // SQLite doesn't have TIMESTAMPDIFF, so we calculate average resolution time in PHP
        $aiResolvedTickets = $aiResolvedQuery->get(['created_at', 'updated_at']);
        $humanResolvedTickets = $humanResolvedQuery->get(['created_at', 'updated_at']);
        
        $aiTotalMinutes = 0;
        foreach($aiResolvedTickets as $t) { $aiTotalMinutes += $t->created_at->diffInMinutes($t->updated_at); }
        $avgAiTime = $aiResolvedCount > 0 ? round($aiTotalMinutes / $aiResolvedCount, 1) : 0;
        
        $humanTotalMinutes = 0;
        $humanResolvedCount = $humanResolvedTickets->count();
        foreach($humanResolvedTickets as $t) { $humanTotalMinutes += $t->created_at->diffInMinutes($t->updated_at); }
        $avgHumanTime = $humanResolvedCount > 0 ? round($humanTotalMinutes / $humanResolvedCount, 1) : 0;

        // Previous Period Queries for Trends
        $prevStartDate = Carbon::now()->subDays($days * 2);
        $prevEndDate = Carbon::now()->subDays($days);
        $prevBaseQuery = Ticket::where('owner_id', $ownerId)->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        
        $prevTotalTickets = (clone $prevBaseQuery)->count();
        $prevAiResolvedCount = (clone $prevBaseQuery)->where('status', 'Resolved')->where('assigned', 'AI Agent')->count();
        $prevAutomationRate = $prevTotalTickets > 0 ? round(($prevAiResolvedCount / $prevTotalTickets) * 100, 1) : 0;
        $prevAvgAiAccuracy = (clone $prevBaseQuery)->whereNotNull('confidence')->avg('confidence') ?? 0;

        $automationTrend = $automationRate - $prevAutomationRate;
        $accuracyTrendVal = round($avgAiAccuracy - $prevAvgAiAccuracy, 1);

        // KPIs
        $kpis = [
            'ai_accuracy' => [
                'value' => round($avgAiAccuracy, 1) . '%',
                'trend' => ($accuracyTrendVal >= 0 ? '+' : '') . $accuracyTrendVal . '%',
                'label' => 'AI Accuracy',
            ],
            'avg_ai_resolution' => [
                'value' => $avgAiTime . ' min',
                'trend' => '', // Hard to calc without full previous dataset
                'label' => 'Avg. AI Resolution',
            ],
            'avg_human_resolution' => [
                'value' => $avgHumanTime . ' min',
                'trend' => '',
                'label' => 'Avg. Human Resolution',
            ],
            'automation_rate' => [
                'value' => $automationRate . '%',
                'trend' => ($automationTrend >= 0 ? '+' : '') . $automationTrend . '%',
                'label' => 'Automation Rate',
            ],
            'csat_score' => [
                'value' => '4.3 / 5',
                'trend' => 'Excellent',
                'label' => 'CSAT Score',
            ],
            'resolved_today' => [
                'value' => $resolvedTodayCount,
                'trend' => 'On Target',
                'label' => 'Resolved Today',
            ],
        ];

        // Ticket Volume (Daily)
        $ticketVolume = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $aiR = Ticket::where('owner_id', $ownerId)->whereDate('created_at', $date->toDateString())->where('assigned', 'AI Agent')->count();
            $manR = Ticket::where('owner_id', $ownerId)->whereDate('created_at', $date->toDateString())->where('assigned', '!=', 'AI Agent')->count();
            $ticketVolume[] = [
                'date' => $date->format('M d'),
                'aiResolved' => $aiR,
                'manual' => $manR,
            ];
        }

        // Category Distribution
        $categories = Ticket::where('owner_id', $ownerId)->where('created_at', '>=', $startDate)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();
            
        $categoryDistribution = [];
        $colors = ['#cae555', '#162943', '#e2e8f0', '#94a3b8', '#3b82f6', '#f59e0b', '#10b981'];
        $cIdx = 0;
        foreach($categories as $cat) {
            if (empty($cat->category)) continue;
            $categoryDistribution[] = [
                'name' => ucfirst($cat->category),
                'value' => $cat->count,
                'fill' => $colors[$cIdx % count($colors)]
            ];
            $cIdx++;
        }

        // Accuracy Trend (Daily Average Confidence)
        $accuracyTrend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $dayTickets = Ticket::where('owner_id', $ownerId)
                ->whereDate('created_at', $date->toDateString())
                ->whereNotNull('confidence')
                ->get();
                
            $avgConf = $dayTickets->count() > 0 ? $dayTickets->avg('confidence') : 0;
            
            $accuracyTrend[] = [
                'date' => $date->format('M d'),
                'accuracy' => round($avgConf, 1),
                'confidence' => round($avgConf, 1), 
            ];
        }

        // Resolution Comparison
        $resolutionComparison = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $aiDay = Ticket::where('owner_id', $ownerId)
                ->whereDate('created_at', $date->toDateString())
                ->where('status', 'Resolved')
                ->where('assigned', 'AI Agent')
                ->get(['created_at', 'updated_at']);
                
            $humDay = Ticket::where('owner_id', $ownerId)
                ->whereDate('created_at', $date->toDateString())
                ->where('status', 'Resolved')
                ->where('assigned', '!=', 'AI Agent')
                ->get(['created_at', 'updated_at']);
                
            $aiMins = 0; foreach($aiDay as $t) $aiMins += $t->created_at->diffInMinutes($t->updated_at);
            $humMins = 0; foreach($humDay as $t) $humMins += $t->created_at->diffInMinutes($t->updated_at);
            
            $resolutionComparison[] = [
                'date' => $date->format('M d'),
                'ai_time' => $aiDay->count() > 0 ? round($aiMins / $aiDay->count(), 1) : 0,
                'human_time' => $humDay->count() > 0 ? round($humMins / $humDay->count(), 1) : 0,
            ];
        }

        return [
            'kpis' => $kpis,
            'ticket_volume' => $ticketVolume,
            'category_distribution' => $categoryDistribution,
            'accuracy_trend' => $accuracyTrend,
            'resolution_comparison' => $resolutionComparison,
        ];
    }
}
