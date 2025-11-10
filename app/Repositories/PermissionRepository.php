<?php

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionRepository implements PermissionRepositoryInterface
{
    protected Permission $model;

    public function __construct(Permission $model)
    {
        $this->model = $model;
    }

    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function find(int $id): ?Permission
    {
        return $this->model->find($id);
    }

    public function findByUuid(string $uuid): ?Permission
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    public function findBySlug(string $slug): ?Permission
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function create(array $data): Permission
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $permission = $this->model->find($id);

        if (! $permission) {
            return false;
        }

        return (bool) $permission->update($data);
    }

    public function delete(int $id): bool
    {
        $permission = $this->model->find($id);

        if (! $permission) {
            return false;
        }

        return (bool) $permission->delete();
    }

    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (! empty($criteria['q'])) {
            $q = $criteria['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('display_name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (! empty($criteria['name'])) {
            $query->where('name', 'like', "%{$criteria['name']}%");
        }

        if (! empty($criteria['slug'])) {
            $query->where('slug', $criteria['slug']);
        }

        return $query->orderBy('id', 'asc')->paginate($perPage);
    }

    public function findWithRelations(int $id, array $relations = []): ?Permission
    {
        return $this->model->with($relations)->find($id);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('status', Permission::STATUS_ACTIVE)->paginate($perPage);
    }

    public function attachToRole(int $permissionId, int $roleId): bool
    {
        $role = Role::find($roleId);

        if (! $role) {
            return false;
        }

        try {
            // Role::givePermission accepts id or Permission instance
            $role->givePermission($permissionId);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function detachFromRole(int $permissionId, int $roleId): bool
    {
        $role = Role::find($roleId);

        if (! $role) {
            return false;
        }

        try {
            $role->removePermission($permissionId);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
