<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PlannerHistory;

interface PlannerHistoryRepositoryInterface
{
    /**
     * Get all planner histories with pagination
     *
     * @param int $perPage
     * @param array $filters
     * @return mixed
     */
    public function getAllPlannerHistories(int $perPage = 10, array $filters = []);

    /**
     * Get planner histories for a specific planner
     *
     * @param int $plannerId
     * @param int $perPage
     * @return mixed
     */
    public function getPlannerHistories(int $plannerId, int $perPage = 10);

    /**
     * Get planner histories for a specific brief
     *
     * @param int $briefId
     * @param int $perPage
     * @return mixed
     */
    public function getBriefPlannerHistories(int $briefId, int $perPage = 10);

    /**
     * Get planner histories by status
     *
     * @param string $status
     * @param int $perPage
     * @return mixed
     */
    public function getByStatus(string $status, int $perPage = 10);

    /**
     * Get recent planner histories
     *
     * @param int $limit
     * @return mixed
     */
    public function getRecentHistories(int $limit = 10);

    /**
     * Create a planner history record
     *
     * @param array $data
     * @return PlannerHistory
     */
    public function createHistory(array $data): PlannerHistory;
}
