<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Services\Owner\AnalyticsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AnalyticsController extends Controller
{
    use ApiResponse;

    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $range = $request->query('range', '7days'); // 7days, 30days, 90days
            $data = $this->analyticsService->getAnalyticsData($range);
            
            return $this->sendResponse(
                $data,
                'Analytics data fetched successfully.'
            );
            
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch analytics data.', [$e->getMessage()], 500);
        }
    }
}
