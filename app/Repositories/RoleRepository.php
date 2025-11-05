<?php

namespace App\Repositories;

use App\Models\Role;
use App\Contracts\Repositories\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleRepository implements RoleRepositoryInterface
{
    /**
     * @var Role
     */
    protected $model;

    /**
     * RoleRepository constructor.
     *
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    // Get all roles.
    public function all(): Collection
    {
        return $this->model->all();
    }

    // Get paginated roles.
    public function allPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('display_name', 'asc')->paginate($perPage);
    }

    // Find a role by its primary key (ID).
    public function findById(int $id): ?Role
    {
        return $this->model->find($id);
    }

    // Find a role by its slug.
    public function findBySlug(string $slug): ?Role
    {
        return $this->model->where('slug', $slug)->first();
    }

    // Find a role by its UUID.
    public function findByUuid(string $uuid): ?Role
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    // Create a new role.
    public function create(array $data): Role
    {
        return $this->model->create($data);
    }

    // Update an existing role.
    public function update(int $id, array $data): Role
    {
        $role = $this->model->findOrFail($id);
        $role->update($data);
        return $role;
    }

    // Soft delete a role by its ID.
    public function delete(int $id): bool
    {
        $role = $this->findById($id);
        if ($role) {
            return $role->delete();
        }
        return false;
    }

    // Restore a soft-deleted role by its ID.
    public function restore(int $id): bool
    {
        $role = $this->model->onlyTrashed()->find($id);
        if ($role) {
            return $role->restore();
        }
        return false;
    }

    // Permanently delete a role by its ID.
    public function forceDelete(int $id): bool
    {
        $role = $this->model->withTrashed()->find($id);
        if ($role) {
            return $role->forceDelete();
        }
        return false;
    }

    // Attach permissions to a role.
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->findById($roleId);
        if ($role) {
            $role->syncPermissions($permissionIds);
        } else {
            throw new ModelNotFoundException("Role with ID {$roleId} not found.");
        }
    }
}