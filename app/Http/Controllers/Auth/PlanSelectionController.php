<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Inertia\Inertia;

class PlanSelectionController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function index()
    {
        $supplierPlans = PricingPlan::where('user_type', 'supplier')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $customerPlans = PricingPlan::where('user_type', 'customer')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return Inertia::render('Auth/SelectPlan', [
            'supplierPlans' => $supplierPlans,
            'customerPlans' => $customerPlans,
        ]);
    }
}
