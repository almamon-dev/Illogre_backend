<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\DashboardOverviewResource;
use App\Services\Owner\DashboardService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getOverviewData();
            
            return $this->sendResponse(
                new DashboardOverviewResource($data), 
                'Dashboard overview data fetched successfully.'
            );
            
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch dashboard data.', [$e->getMessage()], 500);
        }
    }
}
