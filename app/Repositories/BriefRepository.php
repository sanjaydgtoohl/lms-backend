<?php

namespace App\Repositories;

use App\Contracts\Repositories\BriefRepositoryInterface;
use App\Models\Brief;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
     *
     * @param int $perPage The number of items per page.
     * @param string|null $searchTerm Optional search term to filter briefs.
     * @return LengthAwarePaginator
     */
    public function getAllBriefs(int $perPage = 15, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->model->with(self::DEFAULT_RELATIONSHIPS);

        // Apply search filter if search term is provided
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('product_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('uuid', 'LIKE', "%{$searchTerm}%");
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
}
