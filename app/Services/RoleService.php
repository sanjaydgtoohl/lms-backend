<?php

namespace App\Services;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class RoleService
{
	protected RoleRepositoryInterface $roleRepository;
	protected ResponseService $responseService;

	public function __construct(RoleRepositoryInterface $roleRepository, ResponseService $responseService)
	{
		$this->roleRepository = $roleRepository;
		$this->responseService = $responseService;
	}

	/**
	 * Get paginated roles
	 */
	public function list(array $criteria = [], int $perPage = 15): LengthAwarePaginator
	{
		return $this->roleRepository->search($criteria, $perPage);
	}

	/**
	 * Find role by id
	 */
	public function find(int $id): ?Role
	{
		return $this->roleRepository->find($id);
	}

	/**
	 * Find role by uuid
	 */
	public function findByUuid(string $uuid): ?Role
	{
		return $this->roleRepository->findByUuid($uuid);
	}

	/**
	 * Find role by slug
	 */
	public function findBySlug(string $slug): ?Role
	{
		return $this->roleRepository->findBySlug($slug);
	}

	/**
	 * Create a new role and optionally sync permissions
	 *
	 * @throws ValidationException
	 */
	public function create(array $data, array $permissions = []): Role
	{
		$this->validateRoleData($data);

		$role = $this->roleRepository->create($data);

		if (! empty($permissions)) {
			$this->roleRepository->syncPermissions($role->id, $permissions);
		}

		return $role;
	}

	/**
	 * Update a role
	 *
	 * @throws ValidationException
	 */
	public function update(int $id, array $data): bool
	{
		$this->validateRoleData($data, $id);

		return $this->roleRepository->update($id, $data);
	}

	/**
	 * Delete a role
	 */
	public function delete(int $id): bool
	{
		return $this->roleRepository->delete($id);
	}

	/**
	 * Sync permissions for a role
	 */
	public function syncPermissions(int $roleId, array $permissionIds): bool
	{
		return $this->roleRepository->syncPermissions($roleId, $permissionIds);
	}

	/**
	 * Attach a permission to a role
	 */
	public function attachPermission(int $roleId, int $permissionId): bool
	{
		return $this->roleRepository->attachPermission($roleId, $permissionId);
	}

	/**
	 * Detach a permission from a role
	 */
	public function detachPermission(int $roleId, int $permissionId): bool
	{
		return $this->roleRepository->detachPermission($roleId, $permissionId);
	}

	/**
	 * Validate role data
	 *
	 * @param array $data
	 * @param int|null $ignoreId
	 * @throws ValidationException
	 */
	protected function validateRoleData(array $data, ?int $ignoreId = null): void
	{
		$rules = [
			'name' => 'required|string|max:255|unique:roles,name' . ($ignoreId ? ",{$ignoreId}" : ''),
			'slug' => 'required|string|max:255|unique:roles,slug' . ($ignoreId ? ",{$ignoreId}" : ''),
			'display_name' => 'nullable|string|max:255',
			'description' => 'nullable|string|max:1000',
			'status' => 'nullable|in:1,2,15',
		];

		$validator = Validator::make($data, $rules);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}
}

