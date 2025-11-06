<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Services\ResponseService;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected RoleService $roleService;
    protected ResponseService $responseService;

    public function __construct(RoleService $roleService, ResponseService $responseService)
    {
        $this->roleService = $roleService;
        $this->responseService = $responseService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $criteria = $request->only(['q', 'name', 'slug', 'status']);

            $roles = $this->roleService->list($criteria, $perPage);
            // Handle both paginator and collection results
            if ($roles instanceof \Illuminate\Pagination\LengthAwarePaginator || $roles instanceof \Illuminate\Pagination\Paginator) {
                $roles->getCollection()->transform(function ($role) {
                    return new RoleResource($role);
                });
            } elseif ($roles instanceof \Illuminate\Support\Collection) {
                $roles->transform(function ($role) {
                    return new RoleResource($role);
                });
            } else {
                // Fallback: convert to collection and map resources
                $roles = collect($roles)->map(function ($role) {
                    return new RoleResource($role);
                });
            }

            return $this->responseService->paginated($roles, 'Roles retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:1,2,15',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $data = $validator->validated();

            $data['slug'] = Str::slug($data['name']);

            $permissions = $data['permissions'] ?? [];
            
            $role = $this->roleService->create($data, $permissions);
            
            return $this->responseService->created(new RoleResource($role), 'Role created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $role = $this->roleService->find($id);

            if (! $role) {
                return $this->responseService->notFound('Role not found');
            }

            return $this->responseService->success(new RoleResource($role), 'Role retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Define the rules (copied from UpdateRoleRequest and improved)
        $rules = [
            'name' => [
                'sometimes', // 'sometimes' allows updating other fields without sending the name
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($id) // Correctly checks for unique name
            ],
            'display_name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000', // Matches your file
            'status' => 'sometimes|nullable|in:1,2,15',
            'permissions' => 'sometimes|nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];

        // Create the validator
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            // Get the validated data
            $data = $validator->validated();

            // Automatically update slug ONLY if the name was sent
            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Update the role
            $updated = $this->roleService->update($id, $data);

            if (! $updated) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->updated(new RoleResource($role), 'Role updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->roleService->delete($id);

            if (! $deleted) {
                return $this->responseService->notFound('Role not found');
            }

            return $this->responseService->deleted('Role deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Sync permissions for a role
     */
    public function syncPermissions(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $ok = $this->roleService->syncPermissions($id, $request->get('permissions'));

            if (! $ok) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->success(new RoleResource($role), 'Permissions synced successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function attachPermission(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $ok = $this->roleService->attachPermission($id, $request->get('permission_id'));

            if (! $ok) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->success(new RoleResource($role), 'Permission attached successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function detachPermission(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $ok = $this->roleService->detachPermission($id, $request->get('permission_id'));

            if (! $ok) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->success(new RoleResource($role), 'Permission detached successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
