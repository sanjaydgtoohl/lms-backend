<?php

/**
 * MissCampaign Repository
 * -----------------------------------------
 * Handles data access operations for miss campaigns, providing CRUD methods and query logic.
 *
 * @package App\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-09
 */

namespace App\Repositories;

use App\Contracts\Repositories\MissCampaignRepositoryInterface;
use App\Models\MissCampaign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MissCampaignRepository implements MissCampaignRepositoryInterface
{
    /**
     * Default relationships to eager load.
     *
     * @var array<string>
     */
    protected const DEFAULT_RELATIONSHIPS = [
        'brand',
        'leadSource',
        'leadSubSource',
        'mediaType',
        'industry',
        'country',
        'state',
        'city',
        'assignBy',
        'assignTo',
    ];

    /**
     * @var MissCampaign
     */
    protected MissCampaign $model;

    public function __construct(MissCampaign $missCampaign)
    {
        $this->model = $missCampaign;
    }

    public function getAllMissCampaigns(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->model->with(self::DEFAULT_RELATIONSHIPS)->where('status', '1');

        if ($searchTerm !== null && $searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                      $brandQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('leadSource', function ($sourceQuery) use ($searchTerm) {
                      $sourceQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('leadSubSource', function ($subSourceQuery) use ($searchTerm) {
                      $subSourceQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('mediaType', function ($mediaQuery) use ($searchTerm) {
                      $mediaQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('industry', function ($industryQuery) use ($searchTerm) {
                      $industryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('country', function ($countryQuery) use ($searchTerm) {
                      $countryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('state', function ($stateQuery) use ($searchTerm) {
                      $stateQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('city', function ($cityQuery) use ($searchTerm) {
                      $cityQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    public function getMissCampaignList(): ?Collection
    {
        return $this->model
            ->select('id', 'name')
            ->where('status', '1')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getMissCampaignById(int $id): ?MissCampaign
    {
        return $this->model->with(self::DEFAULT_RELATIONSHIPS)->find($id);
    }

    public function getMissCampaignBySlug(string $slug): ?MissCampaign
    {
        return $this->model->with(self::DEFAULT_RELATIONSHIPS)->where('slug', $slug)->first();
    }

    public function createMissCampaign(array $data): MissCampaign
    {
        return $this->model->create($data);
    }

    public function updateMissCampaign(int $id, array $data): bool
    {
        $item = $this->model->findOrFail($id);
        return $item->update($data);
    }

    public function deleteMissCampaign(int $id): bool
    {
        $item = $this->model->findOrFail($id);
        return $item->delete();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $item = $this->model->findOrFail($id);
        return $item->update(['status' => $status]);
    }

    public function assignUser(int $id, int $userId, int $assignBy): bool
    {
        $item = $this->model->findOrFail($id);
        return $item->update([
            'assign_to' => $userId,
            'assign_by' => $assignBy,
        ]);
    }
}
