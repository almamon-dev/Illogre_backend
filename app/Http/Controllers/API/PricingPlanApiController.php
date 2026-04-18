<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PricingPlanResource;
use App\Models\PricingPlan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PricingPlanApiController extends Controller
{
    use ApiResponse;

    /**
     * Get all active pricing plans.
     */
    public function index(Request $request)
    {
        $query = PricingPlan::where('is_active', true);

        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $plans = $query->orderBy('order')->get();

        return $this->sendResponse(
            PricingPlanResource::collection($plans),
            'Pricing plans retrieved successfully.'
        );
    }
}
