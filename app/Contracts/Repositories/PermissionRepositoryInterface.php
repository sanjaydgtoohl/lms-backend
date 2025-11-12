<?php

namespace App\Contracts\Repositories;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface
{
	/**
	 * Get all permissions with pagination
	 *
	 * @param int $perPage
	 * @return LengthAwarePaginator<Permission>
	 */
	public function all(int $perPage = 15): LengthAwarePaginator;

	/**
	 * Find a permission by ID
	 */
	public function find(int $id): ?Permission;

	/**
	 * Find a permission by UUID
	 */
	public function findByUuid(string $uuid): ?Permission;

	/**
	 * Find a permission by name
	 */
	public function findByName(string $name): ?Permission;

	/**
	 * Create a new permission
	 */
	public function create(array $data): Permission;

	/**
	 * Update a permission by ID
	 */
	public function update(int $id, array $data): bool;

	/**
	 * Delete a permission by ID
	 */
	public function delete(int $id): bool;

	/**
	 * Search permissions by criteria with pagination
	 */
	public function search(array $criteria, int $perPage = 15): LengthAwarePaginator;

	/**
	 * Find a permission by ID with relationships
	 */
	public function findWithRelations(int $id, array $relations = []): ?Permission;

	/**
	 * Attach a permission to a role
	 */
	public function attachToRole(int $permissionId, int $roleId): bool;

	/**
	 * Detach a permission from a role
	 */
	public function detachFromRole(int $permissionId, int $roleId): bool;
}

