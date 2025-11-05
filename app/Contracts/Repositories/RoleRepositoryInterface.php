<?php

namespace App\Contracts\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    // Get all roles.
    public function all(): Collection;

    // Get paginated roles.
    public function allPaginated(int $perPage = 15): LengthAwarePaginator;

    // Find a role by its primary key (ID).
    public function findById(int $id): ?Role;

    // Find a role by its slug.
    public function findBySlug(string $slug): ?Role;

    // Find a role by its UUID.
    public function findByUuid(string $uuid): ?Role;

    // Create a new role.
    public function create(array $data): Role;

    // Update an existing role.
    public function update(int $id, array $data): Role;

    // Soft delete a role by its ID.
    public function delete(int $id): bool;

    // Restore a soft-deleted role by its ID.
    public function restore(int $id): bool;

    // Permanently delete a role by its ID.
    public function forceDelete(int $id): bool;

    // Attach permissions to a role.
    public function syncPermissions(int $roleId, array $permissionIds): void;
}