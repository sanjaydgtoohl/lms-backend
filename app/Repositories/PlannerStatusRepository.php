<?php

namespace App\Repositories;

use App\Contracts\Repositories\PlannerStatusRepositoryInterface;
use App\Models\PlannerStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlannerStatusRepository implements PlannerStatusRepositoryInterface
{
    protected PlannerStatus $model;

    public function __construct(PlannerStatus $model)
    {
        $this->model = $model;
    }

    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function find(int $id): ?PlannerStatus
    {
        return $this->model->find($id);
    }

    public function findByUuid(string $uuid): ?PlannerStatus
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    public function findByName(string $name): ?PlannerStatus
    {
        return $this->model->where('name', $name)->first();
    }

    public function findBySlug(string $slug): ?PlannerStatus
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function create(array $data): PlannerStatus
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $plannerStatus = $this->model->find($id);

        if (!$plannerStatus) {
            return false;
        }

        return (bool) $plannerStatus->update($data);
    }

    public function delete(int $id): bool
    {
        $plannerStatus = $this->model->find($id);

        if (!$plannerStatus) {
            return false;
        }

        return (bool) $plannerStatus->delete();
    }

    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Global q parameter for quick search
        if (!empty($criteria['q'])) {
            $q = $criteria['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if (!empty($criteria['name'])) {
            $query->where('name', 'like', "%{$criteria['name']}%");
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        // Order results ascending by id by default
        return $query->orderBy('id', 'asc')->paginate($perPage);
    }
}
