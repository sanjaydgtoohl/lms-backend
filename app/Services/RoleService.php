<?php

namespace App\Services;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class RoleService
{
    protected $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    // Get all roles with pagination.
    public function getRolesPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->roleRepository->allPaginated($perPage);
    }

    // Get all roles.
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->all();
    }

    // Find a single role by its ID.
    public function getRoleById(int $id): ?Role
    {
        return $this->roleRepository->findById($id);
    }

    // Find a single role by its slug.
    public function getRoleBySlug(string $slug): ?Role
    {
        return $this->roleRepository->findBySlug($slug);
    }

    /**
     * Create a new role and sync permissions.
     *
     * @param array $data
     * @return Role
     */
    public function createNewRole(array $data): Role
    {
        // Separate permissions from the main role data
        $permissionIds = Arr::pull($data, 'permissions', []);
        
        // Create the role
        $role = $this->roleRepository->create($data);

        // Sync permissions if any were provided
        if (!empty($permissionIds)) {
            $this->roleRepository->syncPermissions($role->id, $permissionIds);
        }

        return $role;
    }

    /**
     * Update an existing role and sync permissions.
     *
     * @param int $id
     * @param array $data
     * @return Role
     */
    public function updateRole(int $id, array $data): Role
    {
        // Separate permissions from the main role data
        $permissionIds = Arr::pull($data, 'permissions', []);

        // Update the role
        $role = $this->roleRepository->update($id, $data);

        // Sync permissions. If an empty array is passed, it will remove all permissions.
        $this->roleRepository->syncPermissions($role->id, $permissionIds);

        return $role;
    }

    // Soft delete a role.
    public function deleteRole(int $id): bool
    {
        return $this->roleRepository->delete($id);
    }

    // Restore a soft-deleted role.
    public function restoreRole(int $id): bool
    {
        return $this->roleRepository->restore($id);
    }
}