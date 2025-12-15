<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Exception;

class DashboardController extends Controller
{
    /**
     * The dashboard service instance
     *
     * @var DashboardService
     */
    protected $dashboardService;

    /**
     * The response service instance
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Constructor
     *
     * @param DashboardService $dashboardService
     * @param ResponseService $responseService
     */
    public function __construct(DashboardService $dashboardService, ResponseService $responseService)
    {
        $this->dashboardService = $dashboardService;
        $this->responseService = $responseService;
    }

    /**
     * Get dashboard data
     *
     * @return JsonResponse
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $dashboardData = $this->dashboardService->getDashboardData();

            return $this->responseService->success(
                $dashboardData,
                'Dashboard data retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->responseService->serverError(
                'Failed to retrieve dashboard data: ' . $e->getMessage()
            );
        }
    }
}
