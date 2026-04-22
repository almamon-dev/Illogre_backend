<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\PricingPlanFeature;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PricingPlanController extends Controller
{
    use ApiResponse;

    /**
     * Store a new pricing plan with its features and limits.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'billing_period' => 'required|in:trial,monthly,quarterly,annual',
            'limits' => 'nullable|array',
            'display_features' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create Base Plan
            $plan = PricingPlan::create([
                'name' => $request->name,
                'price' => $request->price,
                'billing_period' => $request->billing_period,
                'features' => $request->display_features, // Storing display version in JSON too
                'is_active' => true,
            ]);

            // 2. Save Numeric Limits to features table
            if ($request->has('limits')) {
                foreach ($request->limits as $name => $value) {
                    PricingPlanFeature::create([
                        'pricing_plan_id' => $plan->id,
                        'name' => $name,
                        'value' => $value,
                        'is_limit' => true
                    ]);
                }
            }

            // 3. Save Descriptive Features to table
            if ($request->has('display_features')) {
                foreach ($request->display_features as $featureName) {
                    PricingPlanFeature::create([
                        'pricing_plan_id' => $plan->id,
                        'name' => $featureName,
                        'value' => 'true',
                        'is_limit' => false
                    ]);
                }
            }

            DB::commit();
            return $this->sendResponse($plan->load('planFeatures'), 'Pricing plan created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create plan.', [$e->getMessage()], 500);
        }
    }

    /**
     * Update an existing pricing plan.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'billing_period' => 'required|in:trial,monthly,quarterly,annual',
            'limits' => 'nullable|array',
            'display_features' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $plan = PricingPlan::findOrFail($id);
            
            // 1. Update Base Plan
            $plan->update([
                'name' => $request->name,
                'price' => $request->price,
                'billing_period' => $request->billing_period,
                'features' => $request->display_features,
            ]);

            // 2. Sync Limits & Features (Remove old and add new)
            $plan->planFeatures()->delete();

            if ($request->has('limits')) {
                foreach ($request->limits as $name => $value) {
                    PricingPlanFeature::create([
                        'pricing_plan_id' => $plan->id,
                        'name' => $name,
                        'value' => $value,
                        'is_limit' => true
                    ]);
                }
            }

            if ($request->has('display_features')) {
                foreach ($request->display_features as $featureName) {
                    PricingPlanFeature::create([
                        'pricing_plan_id' => $plan->id,
                        'name' => $featureName,
                        'value' => 'true',
                        'is_limit' => false
                    ]);
                }
            }

            DB::commit();
            return $this->sendResponse($plan->load('planFeatures'), 'Pricing plan updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update plan.', [$e->getMessage()], 500);
        }
    }
}
