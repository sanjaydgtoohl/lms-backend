<?php

namespace App\Repositories;

use App\Contracts\Repositories\BriefRepositoryInterface;
use App\Models\Brief;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BriefRepository implements BriefRepositoryInterface
{
    /**
     * Default relationships to eager load.
     *
     * @var array<string>
     */
    protected const DEFAULT_RELATIONSHIPS = [
        'contactPerson',
        'brand',
        'agency',
        'assignedUser',
        'createdByUser',
        'briefStatus',
        'priority',
    ];

    /**
     * @var Brief
     */
    protected Brief $model;

    /**
     * Create a new BriefRepository instance.
     *
     * @param Brief $brief
     */
    public function __construct(Brief $brief)
    {
        $this->model = $brief;
    }

    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Fetch paginated list of briefs with relationships.
     * Filtered by user access: Only creator and assigned user can see.
     *
     * @param int $perPage The number of items per page.
     * @param string|null $searchTerm Optional search term to filter briefs.
     * @return LengthAwarePaginator
     */
    public function getAllBriefs(int $perPage = 15, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->accessibleToUser(Auth::user());

        // Apply search filter if search term is provided
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('product_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                      $brandQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('contactPerson', function ($contactQuery) use ($searchTerm) {
                      $contactQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('priority', function ($priorityQuery) use ($searchTerm) {
                      $priorityQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('assignedUser', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch a single brief by its primary ID.
     *
     * @param int $id The brief ID.
     * @return Brief|null
     */
    public function getBriefById(int $id): ?Brief
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->find($id);
    }

    /**
     * Fetch briefs by brand ID.
     *
     * @param int $brandId The brand ID.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getBriefsByBrand(int $brandId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('brand_id', $brandId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch briefs by agency ID.
     *
     * @param int $agencyId The agency ID.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getBriefsByAgency(int $agencyId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('agency_id', $agencyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch briefs by assigned user ID.
     *
     * @param int $userId The user ID.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getBriefsByAssignedUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('assign_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch briefs by status ID.
     *
     * @param int $statusId The status ID.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getBriefsByStatus(int $statusId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('brief_status_id', $statusId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch briefs by priority ID.
     *
     * @param int $priorityId The priority ID.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function getBriefsByPriority(int $priorityId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('priority_id', $priorityId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Get brief with all relationships loaded.
     *
     * @param int $id The brief ID.
     * @return Brief|null
     */
    public function getBriefWithRelations(int $id): ?Brief
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->find($id);
    }

    /**
     * Get the latest two briefs.
     *
     * @return Collection
     */
    public function getLatestTwoBriefs()
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();
    }

    /**
     * Get the latest five briefs.
     *
     * @return Collection
     */
    public function getLatestFiveBriefs()
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Search briefs by multiple criteria.
     *
     * @param array $criteria The search criteria.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function searchBriefs(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(self::DEFAULT_RELATIONSHIPS);

        foreach ($criteria as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Get briefs with pagination and filters.
     *
     * @param array $filters The filter criteria.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator
     */
    public function filterBriefs(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(self::DEFAULT_RELATIONSHIPS);

        // Apply filters
        if (isset($filters['brand_id']) && !empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['agency_id']) && !empty($filters['agency_id'])) {
            $query->where('agency_id', $filters['agency_id']);
        }

        if (isset($filters['assign_user_id']) && !empty($filters['assign_user_id'])) {
            $query->where('assign_user_id', $filters['assign_user_id']);
        }

        if (isset($filters['brief_status_id']) && !empty($filters['brief_status_id'])) {
            $query->where('brief_status_id', $filters['brief_status_id']);
        }

        if (isset($filters['priority_id']) && !empty($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('product_name', 'LIKE', "%{$filters['search']}%");
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new brief.
     *
     * @param array $data The brief data.
     * @return Brief
     */
    public function createBrief(array $data): Brief
    {
        try {
            $brief = $this->model->create($data);
            Log::info('Brief created successfully', ['brief_id' => $brief->id]);
            return $brief;
        } catch (\Exception $e) {
            Log::error('Error creating brief', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update an existing brief.
     *
     * @param int $id The brief ID.
     * @param array $data The brief data to update.
     * @return Brief|null
     */
    public function updateBrief(int $id, array $data): ?Brief
    {
        try {
            $brief = $this->model->find($id);
            if (!$brief) {
                return null;
            }

            $brief->update($data);
            Log::info('Brief updated successfully', ['brief_id' => $id]);
            return $brief->fresh(self::DEFAULT_RELATIONSHIPS);
        } catch (\Exception $e) {
            Log::error('Error updating brief', ['brief_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a brief by ID.
     *
     * @param int $id The brief ID.
     * @return bool
     */
    public function deleteBrief(int $id): bool
    {
        try {
            $brief = $this->model->find($id);
            if (!$brief) {
                return false;
            }

            $brief->delete();
            Log::info('Brief deleted successfully', ['brief_id' => $id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting brief', ['brief_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get recent briefs with all related information.
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentBriefs(int $limit = 5)
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->accessibleToUser()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPlannerDashboardCardData(): array
    {
        $now = now();

        // Active briefs (brief_status is not 'closed')
        $activeBriefs = $this->model
            ->whereHas('briefStatus', function ($query) {
                $query->where('slug', '!=', 'closed');
            })
            ->count();

        // Closed briefs (brief_status is 'closed')
        $closedBriefs = $this->model
            ->whereHas('briefStatus', function ($query) {
                $query->where('slug', 'closed');
            })
            ->count();

        // Total of all brief left time during submission (sum of days left for all active briefs/overdue items)
        $totalLeftTime = $this->model
            ->whereHas('briefStatus', function ($query) {
                $query->where('slug', '!=', 'closed');
            })
            ->selectRaw('SUM(DATEDIFF(submission_date, NOW())) as total_days_left')
            ->value('total_days_left');

        // Average planning time (in days - difference between created_at and submission_date)
        $averagePlanningTime = $this->model
            ->selectRaw('AVG(DATEDIFF(submission_date, created_at)) as avg_days')
            ->value('avg_days');

        return [
            'active_briefs' => $activeBriefs,
            'closed_briefs' => $closedBriefs,
            'total_left_time_days' => $totalLeftTime ? max((int)$totalLeftTime, 0) : 0,
            'average_planning_time_days' => $averagePlanningTime ? round($averagePlanningTime, 2) : 0,
        ];
    }

    /**
     * Get brief logs with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBriefLogs(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->accessibleToUser(Auth::user())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    
}
