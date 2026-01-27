<?php

namespace App\Repositories;

use App\Models\Planner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PlannerRepository
{
    /**
     * Default relationships to eager load.
     *
     * @var array<string>
     */
    protected const DEFAULT_RELATIONSHIPS = [
        'brief',
        'creator',
        'plannerStatus',
    ];

    /**
     * @var Planner
     */
    protected Planner $model;

    /**
     * Create a new PlannerRepository instance.
     *
     * @param Planner $planner
     */
    public function __construct(Planner $planner)
    {
        $this->model = $planner;
    }

    /**
     * Fetch paginated list of planners with relationships.
     *
     * @param int $perPage The number of items per page.
     * @param array $filters Optional filters to apply.
     * @return LengthAwarePaginator
     */
    public function getAllPlanners(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(self::DEFAULT_RELATIONSHIPS);

        // Apply filters if provided
        if (!empty($filters)) {
            if (isset($filters['brief_id'])) {
                $query->where('brief_id', $filters['brief_id']);
            }
            if (isset($filters['created_by'])) {
                $query->where('created_by', $filters['created_by']);
            }
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (isset($filters['search'])) {
                $searchTerm = $filters['search'];
                $query->whereHas('brief', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Get planner by ID with relationships.
     *
     * @param int $id
     * @return Planner|null
     */
    public function getPlannerById(int $id): ?Planner
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->find($id);
    }

    /**
     * Get planners by brief ID.
     *
     * @param int $briefId
     * @param int $perPage
     * @param string|null $status
     * @return LengthAwarePaginator
     */
    public function getPlannersByBriefId(int $briefId, int $perPage = 10, ?string $status = null): LengthAwarePaginator
    {
        $query = $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('brief_id', $briefId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get planners by user (creator).
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPlannersByCreator(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('created_by', $userId)
            ->paginate($perPage);
    }

    /**
     * Create a new planner.
     *
     * @param array $data
     * @return Planner
     */
    public function createPlanner(array $data): Planner
    {
        return $this->model->create($data);
    }

    /**
     * Update planner.
     *
     * @param int $id
     * @param array $data
     * @return Planner|null
     */
    public function updatePlanner(int $id, array $data): ?Planner
    {
        $planner = $this->getPlannerById($id);

        if (!$planner) {
            return null;
        }

        $planner->update($data);
        return $planner->refresh()->load(self::DEFAULT_RELATIONSHIPS);
    }

    /**
     * Delete planner.
     *
     * @param int $id
     * @return bool
     */
    public function deletePlanner(int $id): bool
    {
        $planner = $this->model->find($id);

        if (!$planner) {
            return false;
        }

        return $planner->delete();
    }

    /**
     * Get active planners.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActivePlanners(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('status', '1')
            ->paginate($perPage);
    }

    /**
     * Count planners by brief ID.
     *
     * @param int $briefId
     * @return int
     */
    public function countByBriefId(int $briefId): int
    {
        return $this->model->where('brief_id', $briefId)->count();
    }

    /**
     * Check if planner exists by UUID.
     *
     * @param string $uuid
     * @return bool
     */
    public function existsByUuid(string $uuid): bool
    {
        return $this->model->where('uuid', $uuid)->exists();
    }

    /**
     * Get planner by UUID.
     *
     * @param string $uuid
     * @return Planner|null
     */
    public function getPlannerByUuid(string $uuid): ?Planner
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('uuid', $uuid)
            ->first();
    }
}
