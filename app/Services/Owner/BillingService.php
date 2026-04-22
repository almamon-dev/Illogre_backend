<?php

namespace App\Services\Owner;

use App\Models\Payment;
use App\Models\SubscriptionUsage;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Get billing overview data for the authenticated user (owner).
     */
    public function getBillingData(): array
    {
        $user = auth()->user();
        $subscription = $user->subscription()->with('pricingPlan.planFeatures')->first();
        $plan = $subscription ? $subscription->pricingPlan : null;

        // Dynamic Team Members count (Owner + Members)
        $teamMembersCount = User::where('parent_id', $user->id)->count() + 1;
        
        // Limits from plan features table or defaults
        $features = $plan ? $plan->planFeatures : collect();
        
        $ticketTotal = (int) ($features->where('name', 'ticket_limit')->first()?->value ?? 500);
        $memberTotal = (int) ($features->where('name', 'member_limit')->first()?->value ?? 2);
        $aiTotal = (int) ($features->where('name', 'ai_limit')->first()?->value ?? 1000);

        // Fetch dynamic usage from database
        $usages = SubscriptionUsage::where('user_id', $user->id)->get();
        $ticketsUsed = (int) ($usages->where('feature_name', 'ticket_limit')->first()?->used_count ?? 0);
        $aiActionsUsed = (int) ($usages->where('feature_name', 'ai_limit')->first()?->used_count ?? 0);

        // Fetch Dynamic Billing History
        $payments = Payment::where('user_id', $user->id)
            ->with('pricingPlan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payment, $index) {
                return [
                    'sl_no' => $index + 1,
                    'invoice_id' => 'INV-2026-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'date' => $payment->created_at->format('M d, Y'),
                    'description' => ($payment->pricingPlan ? $payment->pricingPlan->name : 'Starter').' Plan',
                    'amount' => '$'.number_format($payment->amount, 0),
                    'status' => ucfirst($payment->status),
                    'action_url' => '#', // Placeholder for download link
                ];
            });

        return [
            'usage' => [
                'tickets_used' => $ticketsUsed,
                'tickets_total' => $ticketTotal,
                'team_members' => $teamMembersCount,
                'team_total' => $memberTotal,
                'ai_actions' => $aiActionsUsed,
                'ai_total' => $aiTotal,
            ],
            'plan' => [
                'name' => $plan ? $plan->name : 'Free Trial',
                'price' => $plan ? $plan->price : 0,
                'description' => $plan ? 'Up to '.number_format($ticketTotal).' tickets' : 'Starter features',
            ],
            'history' => $payments,
        ];
    }

    /**
     * Check if a specific limit has been reached.
     * 
     * @throws Exception
     */
    public function checkLimit(string $feature, $userId = null): bool
    {
        $userId = $userId ?: auth()->id();
        $user = User::with(['subscription.pricingPlan.planFeatures'])->findOrFail($userId);
        
        $plan = $user->subscription ? $user->subscription->pricingPlan : null;
        if (!$plan) {
            // Default limits if no plan (or maybe throw exception if required)
            $limitValue = 0;
        } else {
            $limitFeature = $plan->planFeatures->where('name', $feature)->first();
            $limitValue = $limitFeature ? (int) $limitFeature->value : 0;
        }

        // For member_limit, we count active users
        if ($feature === 'member_limit') {
            $currentUsage = User::where('parent_id', $userId)->count() + 1; // Including owner
        } else {
            // For other features, check usage table
            $usage = SubscriptionUsage::where('user_id', $userId)
                ->where('feature_name', $feature)
                ->first();
            $currentUsage = $usage ? $usage->used_count : 0;
        }

        if ($currentUsage >= $limitValue) {
            return false; // Limit reached
        }

        return true; // Under limit
    }

    /**
     * Increment usage for a specific feature.
     */
    public function trackUsage(string $feature, $userId = null, int $quantity = 1): void
    {
        $userId = $userId ?: auth()->id();

        SubscriptionUsage::updateOrCreate(
            ['user_id' => $userId, 'feature_name' => $feature],
            ['used_count' => DB::raw("used_count + $quantity")]
        );
    }
}
