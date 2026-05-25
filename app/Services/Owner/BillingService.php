<?php

namespace App\Services\Owner;

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
        
        // Use Cashier to check subscription status
        $cashierSubscription = $user->subscription('default');
        $isActive = $user->subscribed('default');
        
        $plan = null;
        $autoRenew = false;
        if ($isActive && $cashierSubscription) {
            $plan = \App\Models\PricingPlan::where('stripe_price_id', $cashierSubscription->stripe_price)->with('planFeatures')->first();
            $autoRenew = !$cashierSubscription->canceled(); // If not canceled, it will auto-renew
        }

        // Dynamic Team Members count (Excluding Owner)
        $teamMembersCount = User::where('parent_id', $user->id)->count();

        // Limits from plan features or 0 if no active plan
        if ($isActive && $plan) {
            $features = $plan->planFeatures;
            $ticketTotal = (int) ($features->where('name', 'ticket_limit')->first()?->value ?? 0);
            $memberTotal = (int) ($features->where('name', 'member_limit')->first()?->value ?? 0);
            $aiTotal = (int) ($features->where('name', 'ai_limit')->first()?->value ?? 0);
        } else {
            $ticketTotal = 0;
            $memberTotal = 0;
            $aiTotal = 0;
        }

        // Fetch dynamic usage from database
        $usages = SubscriptionUsage::where('user_id', $user->id)->get();
        $ticketsUsed = (int) ($usages->where('feature_name', 'ticket_limit')->first()?->used_count ?? 0);
        $aiActionsUsed = (int) ($usages->where('feature_name', 'ai_limit')->first()?->used_count ?? 0);

        // Fetch Dynamic Billing History from Cashier/Stripe
        $payments = [];
        try {
            if ($user->hasStripeId()) {
                $invoices = $user->invoices();
                $payments = collect($invoices)->map(function ($invoice, $index) {
                    $description = $invoice->lines->data[0]->description ?? 'Subscription';
                    return [
                        'sl_no' => $index + 1,
                        'invoice_id' => $invoice->number ?? 'INV-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'date' => $invoice->date()->format('M d, Y'),
                        'description' => $description,
                        'amount' => $invoice->total(),
                        'status' => ucfirst($invoice->status),
                        'action_url' => $invoice->hosted_invoice_url ?? '#',
                    ];
                })->values()->toArray();
            }
        } catch (\Exception $e) {
            // Log or ignore if fetching invoices fails
        }

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
                'is_active' => $isActive,
                'name' => $plan ? $plan->name : 'No Active Plan',
                'price' => $plan ? '$'.number_format($plan->price, 0).'/month' : '$0/month',
                'description' => $plan ? 'Up to '.number_format($ticketTotal).' tickets' : 'Subscribe to a plan to start.',
                'auto_renew' => $autoRenew,
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
        $user = User::findOrFail($userId);
        
        $plan = null;
        if ($user->subscribed('default') && $user->subscription('default')) {
            $plan = \App\Models\PricingPlan::where('stripe_price_id', $user->subscription('default')->stripe_price)->with('planFeatures')->first();
        }

        if (!$plan) {
            // Default limits if no plan (or maybe throw exception if required)
            $limitValue = 0;
        } else {
            $limitFeature = $plan->planFeatures->where('name', $feature)->first();
            $limitValue = $limitFeature ? (int) $limitFeature->value : 0;
        }

        // For member_limit, we count active users (Excluding Owner)
        if ($feature === 'member_limit') {
            $currentUsage = User::where('parent_id', $userId)->count();
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
