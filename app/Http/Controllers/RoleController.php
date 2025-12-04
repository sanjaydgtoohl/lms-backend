<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Services\ResponseService;
use App\Http\Resources\RoleResource;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use ValidatesRequests;

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
            $perPage = (int) $request->get('per_page', 15);
            $criteria = array_filter([
                'q' => $request->input('search'),
                'name' => $request->input('name'),
            ], fn($value) => $value !== null);

            $roles = $this->roleService->list($criteria, $perPage);

            // Apply resource collection to paginated results
            $resource = RoleResource::collection($roles);

            return $this->responseService->paginated($resource, 'Roles retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of roles with only id and name
     */
    public function list(): JsonResponse
    {
        try {
            $roles = $this->roleService->list([], 10000);
            $data = collect($roles->items())->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            });
            return $this->responseService->success($data, 'Roles list retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255|unique:roles,name',
                'slug' => 'nullable|string|max:255',
                'display_name' => 'nullable|string|max:255',
                'description' => 'required|string',
                'status' => 'nullable|string|max:50',
                'permissions' => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
                'permission' => 'nullable|array',
                'permission.*' => 'integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            // Handle both 'permissions' and 'permission[]' parameter names
            $permissions = $validatedData['permissions'] ?? $validatedData['permission'] ?? [];
            unset($validatedData['permissions']);
            unset($validatedData['permission']);
            
            // Explicitly get slug from request if not in validated data
            if (empty($validatedData['slug'])) {
                $validatedData['slug'] = $request->input('slug');
            }
            
            // Create role with permissions
            $role = $this->roleService->create($validatedData, $permissions);
            
            // Reload role with permissions relationship
            $role->load('permissions');
            
            return $this->responseService->created(new RoleResource($role), 'Role created successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
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

            // Load permissions relationship
            $role->load('permissions');

            return $this->responseService->success(new RoleResource($role), 'Role retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $rules = [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles', 'name')->ignore($id)
                ],
                'slug' => 'sometimes|nullable|string|max:255',
                'display_name' => 'sometimes|nullable|string|max:255',
                'description' => 'sometimes|required|string|max:1000',
                'status' => 'sometimes|nullable|string|max:50',
                'permissions' => 'sometimes|nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
                'permission' => 'sometimes|nullable|array',
                'permission.*' => 'integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            // Handle both 'permissions' and 'permission[]' parameter names
            $permissions = $validatedData['permissions'] ?? $validatedData['permission'] ?? null;
            unset($validatedData['permissions']);
            unset($validatedData['permission']);

            // Update the role
            $updated = $this->roleService->update($id, $validatedData, $permissions);

            if (! $updated) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            // Reload role with permissions relationship
            $role->load('permissions');
            
            return $this->responseService->updated(new RoleResource($role), 'Role updated successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
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
        try {
            $rules = [
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $ok = $this->roleService->syncPermissions($id, $validatedData['permissions']);

            if (! $ok) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->success(new RoleResource($role), 'Permissions synced successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function attachPermission(Request $request, $id): JsonResponse
    {
        try {
            $rules = [
                'permission_id' => 'required|integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $ok = $this->roleService->attachPermission($id, $validatedData['permission_id']);

            if (! $ok) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->success(new RoleResource($role), 'Permission attached successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function detachPermission(Request $request, $id): JsonResponse
    {
        try {
            $rules = [
                'permission_id' => 'required|integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $ok = $this->roleService->detachPermission($id, $validatedData['permission_id']);

            if (! $ok) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
            return $this->responseService->success(new RoleResource($role), 'Permission detached successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
