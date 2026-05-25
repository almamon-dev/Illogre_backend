<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Cashier\Cashier;

class PricingPlanController extends Controller
{
    /**
     * Display a listing of pricing plans.
     */
    public function index()
    {
        $plans = PricingPlan::with('planFeatures')->orderBy('order')->get();

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
     * Store a newly created pricing plan in storage and Stripe.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,annual',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'order' => 'nullable|integer',
            'display_features' => 'nullable|array',
            'limits' => 'nullable|array',
        ]);

        try {
            $stripe = Cashier::stripe();

            // 1. Create Product in Stripe
            $product = $stripe->products->create([
                'name' => $validated['name'],
                'active' => $validated['is_active'] ?? true,
            ]);

            // 2. Create Price in Stripe
            $price = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $validated['price'] * 100, // Convert to cents
                'currency' => env('STRIPE_CURRENCY', 'usd'),
                'recurring' => ['interval' => $validated['billing_period'] === 'annual' ? 'year' : 'month'],
            ]);

            // 3. Save to local Database
            $plan = PricingPlan::create([
                'name' => $validated['name'],
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id,
                'price' => $validated['price'],
                'billing_period' => $validated['billing_period'],
                'trial_days' => $validated['trial_days'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'is_popular' => $validated['is_popular'] ?? false,
                'order' => $validated['order'] ?? 0,
            ]);

            // Save features
            $this->saveFeatures($plan, $request);

            return redirect()->route('admin.pricing-plans.index')
                ->with('success', 'Pricing plan created successfully in local and Stripe.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create plan in Stripe: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for editing the specified pricing plan.
     */
    public function edit(PricingPlan $pricingPlan)
    {
        $pricingPlan->load('planFeatures');
        return Inertia::render('Admin/PricingPlans/Edit', [
            'pricingPlan' => $pricingPlan,
        ]);
    }

    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'order' => 'nullable|integer',
            'display_features' => 'nullable|array',
            'limits' => 'nullable|array',
        ]);

        try {
            // Update Stripe Product if name or active status changed
            if ($pricingPlan->stripe_product_id) {
                $stripe = Cashier::stripe();
                $stripe->products->update($pricingPlan->stripe_product_id, [
                    'name' => $validated['name'],
                    'active' => $validated['is_active'] ?? true,
                ]);
            }

            $pricingPlan->update([
                'name' => $validated['name'],
                'trial_days' => $validated['trial_days'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'is_popular' => $validated['is_popular'] ?? false,
                'order' => $validated['order'] ?? 0,
            ]);

            $this->saveFeatures($pricingPlan, $request);

            return redirect()->route('admin.pricing-plans.index')
                ->with('success', 'Pricing plan updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update plan in Stripe: ' . $e->getMessage());
        }
    }

    private function saveFeatures(PricingPlan $plan, Request $request)
    {
        $plan->planFeatures()->delete(); // Clear old features

        if ($request->has('display_features') && is_array($request->display_features)) {
            foreach ($request->display_features as $feature) {
                if (trim($feature)) {
                    $plan->planFeatures()->create([
                        'name' => trim($feature),
                        'value' => null,
                        'is_limit' => false,
                    ]);
                }
            }
        }

        if ($request->has('limits') && is_array($request->limits)) {
            foreach ($request->limits as $key => $value) {
                $plan->planFeatures()->create([
                    'name' => $key,
                    'value' => (string)$value,
                    'is_limit' => true,
                ]);
            }
        }
    }

    /**
     * Remove the specified pricing plan.
     */
    public function destroy(PricingPlan $pricingPlan)
    {
        try {
            if ($pricingPlan->stripe_product_id) {
                $stripe = Cashier::stripe();
                $stripe->products->update($pricingPlan->stripe_product_id, ['active' => false]);
            }
            $pricingPlan->delete();

            return redirect()->route('admin.pricing-plans.index')
                ->with('success', 'Pricing plan archived in Stripe and deleted locally.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete plan in Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(PricingPlan $pricingPlan)
    {
        try {
            $newStatus = !$pricingPlan->is_active;

            if ($pricingPlan->stripe_product_id) {
                $stripe = Cashier::stripe();
                $stripe->products->update($pricingPlan->stripe_product_id, ['active' => $newStatus]);
            }

            $pricingPlan->update(['is_active' => $newStatus]);

            return redirect()->back()
                ->with('success', 'Plan status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update status in Stripe: ' . $e->getMessage());
        }
    }
}
