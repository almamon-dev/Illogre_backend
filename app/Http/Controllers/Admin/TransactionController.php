<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Laravel\Cashier\Cashier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = [];
        $totalRevenue = 0;

        try {
            $stripe = Cashier::stripe();
            
            // Get recent invoices (payments)
            $invoices = $stripe->invoices->all(['limit' => 15, 'expand' => ['data.customer']]);
            
            foreach ($invoices->data as $invoice) {
                $transactions[] = [
                    'id' => $invoice->id,
                    'user' => [
                        'name' => $invoice->customer->name ?? $invoice->customer_email,
                        'email' => $invoice->customer_email,
                    ],
                    'pricing_plan' => [
                        'name' => $invoice->lines->data[0]->description ?? 'Subscription',
                    ],
                    'amount' => $invoice->total / 100, // In dollars
                    'status' => $invoice->status,
                    'created_at' => \Carbon\Carbon::createFromTimestamp($invoice->created)->format('Y-m-d H:i:s'),
                ];

                if ($invoice->status === 'paid') {
                    $totalRevenue += ($invoice->total / 100);
                }
            }
        } catch (\Exception $e) {
            // Handle error or just return empty
        }

        // We wrap it in a mock paginator structure for Inertia/Frontend
        $paginatedTransactions = [
            'data' => $transactions,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => count($transactions),
        ];

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $paginatedTransactions,
            'stats' => [
                'total_revenue' => collect($transactions)->where('status', 'paid')->sum('amount'), // Alternatively use $totalRevenue
            ]
        ]);
    }

    public function show($transactionId)
    {
        try {
            $stripe = Cashier::stripe();
            $invoice = $stripe->invoices->retrieve($transactionId, ['expand' => ['customer']]);
            
            $transaction = [
                'id' => $invoice->id,
                'amount' => $invoice->total / 100,
                'status' => $invoice->status,
                'created_at' => \Carbon\Carbon::createFromTimestamp($invoice->created)->format('Y-m-d H:i:s'),
                'hosted_invoice_url' => $invoice->hosted_invoice_url,
                'invoice_pdf' => $invoice->invoice_pdf,
                'user' => [
                    'name' => $invoice->customer->name ?? $invoice->customer_email,
                    'email' => $invoice->customer_email,
                ],
                'pricing_plan' => [
                    'name' => $invoice->lines->data[0]->description ?? 'Subscription',
                ],
            ];

            return Inertia::render('Admin/Transactions/Show', [
                'transaction' => (object)$transaction
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.transactions.index')->with('error', 'Transaction not found.');
        }
    }
}

