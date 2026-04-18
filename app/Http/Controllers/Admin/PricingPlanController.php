<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PricingPlanController extends Controller
{
    /**
     * Display a listing of pricing plans.
     */
    public function index()
    {
        $plans = PricingPlan::orderBy('order')->get();

        return Inertia::render('Admin/PricingPlans/Index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Show the form for creating a new pricing plan.
     */
    public function create()
    {
        return Inertia::render('Admin/PricingPlans/Create');
    }

    /**
     * Store a newly created pricing plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:trial,monthly,quarterly,annual',
            'trial_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        PricingPlan::create($validated);

        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'Pricing plan created successfully.');
    }

    /**
     * Show the form for editing the specified pricing plan.
     */
    public function edit(PricingPlan $pricingPlan)
    {
        return Inertia::render('Admin/PricingPlans/Edit', [
            'pricingPlan' => $pricingPlan,
        ]);
    }

    /**
     * Update the specified pricing plan.
     */
    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:trial,monthly,quarterly,annual',
            'trial_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $pricingPlan->update($validated);

        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'Pricing plan updated successfully.');
    }

    /**
     * Remove the specified pricing plan.
     */
    public function destroy(PricingPlan $pricingPlan)
    {
        $pricingPlan->delete();

        return redirect()->route('admin.pricing-plans.index')
            ->with('success', 'Pricing plan deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(PricingPlan $pricingPlan)
    {
        $pricingPlan->update([
            'is_active' => ! $pricingPlan->is_active,
        ]);

        return redirect()->back()
            ->with('success', 'Plan status updated successfully.');
    }
}
