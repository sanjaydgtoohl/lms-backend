<?php

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

        if ($searchTerm) {
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
}
