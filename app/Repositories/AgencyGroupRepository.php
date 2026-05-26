<?php

namespace App\Repositories;

use App\Contracts\Repositories\AgencyGroupRepositoryInterface;
use App\Models\Agency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class AgencyGroupRepository implements AgencyGroupRepositoryInterface
{
    protected Agency $model;

    public function __construct(Agency $agency)
    {
        $this->model = $agency;
    }

    /**
     * Parent agencies (agency groups) have no parent reference.
     */
    protected function baseQuery()
    {
        return $this->model
            ->with(['agencyType', 'brand', 'childs'])
            ->whereNull('is_parent')
            ->where('status', '1');
    }

    public function getAllGroups(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('slug', 'LIKE', "%{$searchTerm}%");
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    public function getGroupById(int $id): ?Agency
    {
        return $this->baseQuery()->find($id);
    }

    public function createGroup(array $data): Agency
    {
        $data['is_parent'] = null;

        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->model->create($data);
    }

    public function updateGroup(int $id, array $data): Agency
    {
        $group = $this->baseQuery()->findOrFail($id);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Groups remain top-level parents
        $data['is_parent'] = null;

        $group->update($data);

        return $group->fresh(['agencyType', 'brand', 'childs']);
    }

    public function deleteGroup(int $id): bool
    {
        $group = $this->baseQuery()->findOrFail($id);

        return $group->delete();
    }
}
