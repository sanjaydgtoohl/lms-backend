<?php

namespace App\Http\Controllers;

use App\Http\Resources\MissCampaignHistoryResource;
use App\Services\MissCampaignHistoryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MissCampaignHistoryController extends Controller
{
    protected $service;
    protected $responseService;

    public function __construct(MissCampaignHistoryService $service, ResponseService $responseService)
    {
        $this->service = $service;
        $this->responseService = $responseService;
    }

    // Get all history records for a given miss_campaign_id
    public function getByMissCampaignId($id): JsonResponse
    {
        $histories = $this->service->getByMissCampaignId($id);

        return $this->responseService->success(
            MissCampaignHistoryResource::collection($histories),
            'Miss campaign history retrieved successfully'
        );
    }

    public function show($id): JsonResponse
    {
        $history = $this->service->getById($id);
        if (!$history) {
            return $this->responseService->notFound('History record not found');
        }
        return $this->responseService->success(
            new MissCampaignHistoryResource($history),
            'History record retrieved successfully'
        );
    }
}
