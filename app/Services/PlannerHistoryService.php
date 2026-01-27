<?php

namespace App\Services;

use App\Repositories\PlannerHistoryRepository;
use App\Models\PlannerHistory;
use App\Contracts\Repositories\PlannerHistoryRepositoryInterface;

class PlannerHistoryService
{
    /**
     * @var PlannerHistoryRepository
     */
    protected PlannerHistoryRepositoryInterface $repository;

    /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    public function __construct(PlannerHistoryRepositoryInterface $repository, ResponseService $responseService)
    {
        $this->repository = $repository;
        $this->responseService = $responseService;
    }

    /**
     * Get all planner histories with filters
     *
     * @param int $perPage
     * @param array $filters
     * @return mixed
     */
    public function getAllPlannerHistories(int $perPage = 10, array $filters = [])
    {
        try {
            return $this->repository->getAllPlannerHistories($perPage, $filters);
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to fetch planner histories: ' . $e->getMessage());
        }
    }

    /**
     * Get planner histories for a specific planner
     *
     * @param int $plannerId
     * @param int $perPage
     * @return mixed
     */
    public function getPlannerHistories(int $plannerId, int $perPage = 10)
    {
        try {
            return $this->repository->getPlannerHistories($plannerId, $perPage);
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to fetch planner histories: ' . $e->getMessage());
        }
    }

    /**
     * Get planner histories for a specific brief
     *
     * @param int $briefId
     * @param int $perPage
     * @return mixed
     */
    public function getBriefPlannerHistories(int $briefId, int $perPage = 10)
    {
        try {
            return $this->repository->getBriefPlannerHistories($briefId, $perPage);
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to fetch brief planner histories: ' . $e->getMessage());
        }
    }

    /**
     * Get planner histories by status
     *
     * @param string $status
     * @param int $perPage
     * @return mixed
     */
    public function getByStatus(string $status, int $perPage = 10)
    {
        try {
            return $this->repository->getByStatus($status, $perPage);
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to fetch planner histories by status: ' . $e->getMessage());
        }
    }

    /**
     * Get recent planner histories
     *
     * @param int $limit
     * @return mixed
     */
    public function getRecentHistories(int $limit = 10)
    {
        try {
            return $this->repository->getRecentHistories($limit);
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to fetch recent planner histories: ' . $e->getMessage());
        }
    }

    /**
     * Create a planner history record
     *
     * @param array $data
     * @return PlannerHistory|null
     */
    public function createHistory(array $data): ?PlannerHistory
    {
        try {
            return $this->repository->createHistory($data);
        } catch (\Exception $e) {
            return null;
        }
    }
}
