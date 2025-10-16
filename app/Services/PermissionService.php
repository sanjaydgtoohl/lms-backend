<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    /**
     * The response service instance
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Constructor
     *
     * @param ResponseService $responseService
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Check if user has permission
     *
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function userHasPermission(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    /**
     * Check if user has role
     *
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function userHasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Check if user has any of the given permissions
     *
     * @param User $user
     * @param array $permissions
     * @return bool
     */
    public function userHasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the given permissions
     *
     * @param User $user
     * @param array $permissions
     * @return bool
     */
    public function userHasAllPermissions(User $user, array $permissions): bool
    {
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Assign role to user
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function assignRoleToUser(User $user, Role $role): bool
    {
        try {
            $user->assignRole($role);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove role from user
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function removeRoleFromUser(User $user, Role $role): bool
    {
        try {
            $user->removeRole($role);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Give permission to user
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function givePermissionToUser(User $user, Permission $permission): bool
    {
        try {
            $user->givePermission($permission);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove permission from user
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function removePermissionFromUser(User $user, Permission $permission): bool
    {
        try {
            $user->removePermission($permission);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Give permission to role
     *
     * @param Role $role
     * @param Permission $permission
     * @return bool
     */
    public function givePermissionToRole(Role $role, Permission $permission): bool
    {
        try {
            $role->givePermission($permission);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove permission from role
     *
     * @param Role $role
     * @param Permission $permission
     * @return bool
     */
    public function removePermissionFromRole(Role $role, Permission $permission): bool
    {
        try {
            $role->removePermission($permission);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create role with permissions
     *
     * @param array $roleData
     * @param array $permissions
     * @return Role
     * @throws ValidationException
     */
    public function createRoleWithPermissions(array $roleData, array $permissions = []): Role
    {
        $this->validateRoleData($roleData);

        $role = Role::create($roleData);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    /**
     * Create permission
     *
     * @param array $permissionData
     * @return Permission
     * @throws ValidationException
     */
    public function createPermission(array $permissionData): Permission
    {
        $this->validatePermissionData($permissionData);

        return Permission::create($permissionData);
    }

    /**
     * Get user permissions (including role permissions)
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPermissions(User $user)
    {
        return $user->getAllPermissions();
    }

    /**
     * Get user roles
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRoles(User $user)
    {
        return $user->roles;
    }

    /**
     * Validate role data
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateRoleData(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'level' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate permission data
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validatePermissionData(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'description' => 'nullable|string|max:500',
            'resource' => 'required|string|max:100',
            'action' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
