<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\BillingOverviewResource;
use App\Services\Owner\BillingService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class BillingController extends Controller
{
    use ApiResponse;

    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->billingService->getBillingData();
            
            return $this->sendResponse(
                new BillingOverviewResource($data), 
                'Billing and subscription data fetched successfully.'
            );
            
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch billing data.', [$e->getMessage()], 500);
        }
    }
}
