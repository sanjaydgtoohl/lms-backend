<?php

namespace App\Http\Controllers;
use App\Models\Permission;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Services\PermissionService;
use App\Services\ResponseService;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;
    protected ResponseService $responseService;

    public function __construct(PermissionService $permissionService, ResponseService $responseService)
    {
        $this->permissionService = $permissionService;
        $this->responseService = $responseService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $criteria = $request->only(['q', 'name', 'slug', 'status']);

            $permissions = $this->permissionService->list($criteria, $perPage);

            if ($permissions instanceof \Illuminate\Pagination\LengthAwarePaginator || $permissions instanceof \Illuminate\Pagination\Paginator) {
                $permissions->getCollection()->transform(function ($permission) {
                    return new PermissionResource($permission);
                });
            } elseif ($permissions instanceof \Illuminate\Support\Collection) {
                $permissions->transform(function ($permission) {
                    return new PermissionResource($permission);
                });
            } else {
                $permissions = collect($permissions)->map(function ($permission) {
                    return new PermissionResource($permission);
                });
            }

            return $this->responseService->paginated($permissions, 'Permissions retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:1,2,15',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $data = $validator->validated();

            $data['slug'] = Str::slug($data['name']);

            $permission = $this->permissionService->create($data);

            return $this->responseService->created(new PermissionResource($permission), 'Permission created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $permission = $this->permissionService->find($id);

            if (! $permission) {
                return $this->responseService->notFound('Permission not found');
            }

            return $this->responseService->success(new PermissionResource($permission), 'Permission retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $rules = [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($id),
            ],
            'display_name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'status' => 'sometimes|nullable|in:1,2,15',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $data = $validator->validated();

            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $updated = $this->permissionService->update($id, $data);

            if (! $updated) {
                return $this->responseService->notFound('Permission not found');
            }

            $permission = $this->permissionService->find($id);
            return $this->responseService->updated(new PermissionResource($permission), 'Permission updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->permissionService->delete($id);

            if (! $deleted) {
                return $this->responseService->notFound('Permission not found');
            }

            return $this->responseService->deleted('Permission deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
