<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService
{
    protected PermissionRepositoryInterface $permissionRepository;
    protected ResponseService $responseService;

    public function __construct(PermissionRepositoryInterface $permissionRepository, ResponseService $responseService)
    {
        $this->permissionRepository = $permissionRepository;
        $this->responseService = $responseService;
    }

    /**
     * List permissions with optional criteria
     */
    public function list(array $criteria = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->permissionRepository->search($criteria, $perPage);
    }

    /**
     * Find permission by id
     */
    public function find(int $id): ?Permission
    {
        return $this->permissionRepository->find($id);
    }

    /**
     * Find permission by uuid
     */
    public function findByUuid(string $uuid): ?Permission
    {
        return $this->permissionRepository->findByUuid($uuid);
    }

    /**
     * Find permission by slug
     */
    public function findBySlug(string $slug): ?Permission
    {
        return $this->permissionRepository->findBySlug($slug);
    }

    /**
     * Create a new permission
     *
     * @throws ValidationException
     */
    public function create(array $data): Permission
    {
        $this->validatePermissionData($data);

        return $this->permissionRepository->create($data);
    }

    /**
     * Update a permission
     *
     * @throws ValidationException
     */
    public function update(int $id, array $data): bool
    {
        $this->validatePermissionData($data, $id);

        return $this->permissionRepository->update($id, $data);
    }

    /**
     * Delete a permission
     */
    public function delete(int $id): bool
    {
        return $this->permissionRepository->delete($id);
    }

    /**
     * Get only active permissions
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->permissionRepository->getActive($perPage);
    }

    /**
     * Attach permission to role
     */
    public function attachToRole(int $permissionId, int $roleId): bool
    {
        return $this->permissionRepository->attachToRole($permissionId, $roleId);
    }

    /**
     * Detach permission from role
     */
    public function detachFromRole(int $permissionId, int $roleId): bool
    {
        return $this->permissionRepository->detachFromRole($permissionId, $roleId);
    }

    /**
     * Validate permission data
     *
     * @param array $data
     * @param int|null $ignoreId
     * @throws ValidationException
     */
    protected function validatePermissionData(array $data, ?int $ignoreId = null): void
    {
        $rules = [
            'name' => 'required|string|max:255|unique:permissions,name' . ($ignoreId ? ",{$ignoreId}" : ''),
            'slug' => 'nullable|string|max:255|unique:permissions,slug' . ($ignoreId ? ",{$ignoreId}" : ''),
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
