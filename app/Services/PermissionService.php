<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
     * Find permission by name (replaces findBySlug since slug doesn't exist)
     */
    public function findByName(string $name): ?Permission
    {
        return $this->permissionRepository->findByName($name);
    }

    /**
     * Create a new permission or multiple permissions with parent-child relationship
     *
     * @throws ValidationException
     */
    public function create(array $data): Permission
    {
        // Handle bulk creation if name is an array
        if (isset($data['name']) && is_array($data['name'])) {
            $names = $data['name'];
            $displayNames = $data['display_name'] ?? [];
            $descriptions = $data['description'] ?? [];
            
            // Create the first permission as parent with is_parent = null
            $firstPermissionData = [
                'name' => $names[0],
                'display_name' => $displayNames[0] ?? null,
                'description' => $descriptions[0] ?? null,
                'is_parent' => null,
                'slug' => Str::slug($names[0]),
            ];
            
            $this->validatePermissionData($firstPermissionData);
            $permission = $this->permissionRepository->create($firstPermissionData);
            
            // Create remaining permissions as children with is_parent = parent id
            for ($i = 1; $i < count($names); $i++) {
                $permissionData = [
                    'name' => $names[$i],
                    'display_name' => $displayNames[$i] ?? null,
                    'description' => $descriptions[$i] ?? null,
                    'is_parent' => $permission->id,  // Set parent ID
                    'slug' => Str::slug($names[$i]),
                ];
                $this->validatePermissionData($permissionData);
                $this->permissionRepository->create($permissionData);
            }
            
            return $permission;
        }

        // Generate slug from name if not provided
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

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
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
