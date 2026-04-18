<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Payment::with(['user', 'plan'])
            ->latest()
            ->paginate(15);

        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'stats' => [
                'total_revenue' => $totalRevenue,
            ]
        ]);
    }

    public function show(\App\Models\Payment $transaction)
    {
        $transaction->load(['user', 'plan']);

        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction
        ]);
    }
}

