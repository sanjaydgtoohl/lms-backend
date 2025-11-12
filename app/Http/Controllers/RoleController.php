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
            $perPage = (int) $request->get('per_page', 10);
            $criteria = $request->only(['q', 'name']);

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
        try {
            $rules = [
                'name' => 'required|string|max:255|unique:roles,name',
                'display_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $permissions = $validatedData['permissions'] ?? [];
            unset($validatedData['permissions']);
            
            $role = $this->roleService->create($validatedData, $permissions);
            
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
                'display_name' => 'sometimes|nullable|string|max:255',
                'description' => 'sometimes|nullable|string|max:1000',
                'permissions' => 'sometimes|nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $permissions = $validatedData['permissions'] ?? null;
            if (isset($validatedData['permissions'])) {
                unset($validatedData['permissions']);
            }

            // Update the role
            $updated = $this->roleService->update($id, $validatedData, $permissions);

            if (! $updated) {
                return $this->responseService->notFound('Role not found');
            }

            $role = $this->roleService->find($id);
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
