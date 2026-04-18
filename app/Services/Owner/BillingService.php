<?php

namespace App\Services\Owner;

use App\Models\Payment;

class BillingService
{
    public function getBillingData(): array
    {
        $user = auth()->user();
        $subscription = $user->subscription()->first();
        $plan = $subscription ? $subscription->plan : null;

        // Dynamic Team Members count
        $teamMembersCount = $user->members()->count() + 1;
        $teamTotal = 10;

        // Fetch Dynamic Billing History
        $payments = Payment::where('user_id', $user->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payment, $index) {
                return [
                    'id' => $index + 1,
                    'invoice_id' => 'INV-'.strtoupper(substr($payment->external_payment_id ?? '000', -6)),
                    'date' => $payment->created_at->format('M d, Y'),
                    'description' => $payment->plan ? $payment->plan->name.' Plan' : 'Subscription',
                    'amount' => '$'.number_format($payment->amount, 0),
                    'status' => ucfirst($payment->status),

                ];
            });

        return [
            'usage' => [
                'tickets_used' => 1041,
                'tickets_total' => 2500,
                'team_members' => $teamMembersCount,
                'team_total' => $teamTotal,
                'ai_actions' => 8241,
                'ai_total' => 10000,
                'storage_used_gb' => 4.2,
                'storage_total_gb' => 10,
            ],
            'plan' => [
                'name' => $plan ? $plan->name : 'No Active Plan',
                'price' => $plan ? $plan->price : 0,
                'description' => $plan ? 'Up to 2,500 tickets' : 'Upgrade to get more features',
            ],
            'history' => $payments,
        ];
    }
}
