<?php

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
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

    public function findByName(string $name): ?Permission
    {
        return $this->model->where('name', $name)->first();
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

        // Global q parameter for quick search
        if (! empty($criteria['q'])) {
            $q = $criteria['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('display_name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (! empty($criteria['name'])) {
            $query->where('name', 'like', "%{$criteria['name']}%");
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['is_parent'])) {
            $query->where('is_parent', $criteria['is_parent']);
        }

        // Order results ascending by id by default
        return $query->orderBy('id', 'asc')->paginate($perPage);
    }

    public function findWithRelations(int $id, array $relations = []): ?Permission
    {
        return $this->model->with($relations)->find($id);
    }

    public function syncRoles(int $permissionId, array $roleIds): bool
    {
        $permission = $this->model->find($permissionId);

        if (! $permission) {
            return false;
        }

        try {
            $permission->roles()->sync($roleIds);
            return true;
        } catch (\Throwable $e) {
            // Optionally log the exception here
            return false;
        }
    }

    public function attachRole(int $permissionId, int $roleId): bool
    {
        $permission = $this->model->find($permissionId);

        if (! $permission) {
            return false;
        }

        try {
            $role = \App\Models\Role::find($roleId);
            if (!$role) {
                return false;
            }
            $permission->roles()->syncWithoutDetaching([$roleId]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function detachRole(int $permissionId, int $roleId): bool
    {
        $permission = $this->model->find($permissionId);

        if (! $permission) {
            return false;
        }

        try {
            $permission->roles()->detach($roleId);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getByParentStatus(bool $isParent, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_parent', $isParent)
            ->latest()
            ->paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->active()
            ->latest()
            ->paginate($perPage);
    }

    public function getAllParentPermissions(): array
    {
        return $this->model->whereNotNull('is_parent')
            ->where('is_parent', '!=', 0)
            ->select('id', 'display_name')
            ->orderBy('display_name', 'asc')
            ->get()
            ->toArray();
    }

    public function getAllPermissionTree(): array
    {
        // Get all permissions with id, display_name, and is_parent
        $allPermissions = $this->model->select('id', 'display_name', 'is_parent')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();

        return $this->buildTree($allPermissions);
    }

    /**
     * Build hierarchical tree structure from flat permissions array
     *
     * @param array $permissions
     * @param int|null $parentId
     * @return array
     */
    private function buildTree(array $permissions, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($permissions as $permission) {
            // Check if this permission belongs to the current parent level
            if ($permission['is_parent'] == $parentId) {
                $item = [
                    'id' => $permission['id'],
                    'display_name' => $permission['display_name'],
                ];

                // Recursively get children for this permission
                $subTree = $this->buildTree($permissions, $permission['id']);
                if (!empty($subTree)) {
                    $item[] = $subTree;
                }

                $tree[] = $item;
            }
        }

        return $tree;
    }
}

