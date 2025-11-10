<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusGroupResource;
use App\Services\StatusGroupService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;
use Throwable;

class StatusGroupController extends Controller
{
    protected $statusGroupService;
    protected $responseService;

    public function __construct(
        StatusGroupService $statusGroupService,
        ResponseService $responseService
    ) {
        $this->statusGroupService = $statusGroupService;
        $this->responseService = $responseService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $filters = $request->only(['name', 'sort_by', 'sort_direction']);
            
            $statusGroups = $this->statusGroupService->getAllActivePaginated($perPage, $filters);
            $data = StatusGroupResource::collection($statusGroups);
            
            return $this->responseService->paginated($data, 'Status groups retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $statusGroup = $this->statusGroupService->createGroup($request->all());
            Log::info('Status group created successfully:', ['id' => $statusGroup->id]);

            return $this->responseService->created(
                new StatusGroupResource($statusGroup),
                'Status group created successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $statusGroup = $this->statusGroupService->getGroupById($id); 
            return $this->responseService->success(
                new StatusGroupResource($statusGroup),
                'Status group retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:status_groups,name,' . $id,
                'status_id' => 'sometimes|required|array',
                'status_id.*' => 'integer',
                'status' => 'sometimes|in:1,2,15',
            ]);

            if ($validator->fails()) {
                return $this->responseService->error(
                    'Validation failed',
                    $validator->errors()->toArray(),
                    ResponseService::HTTP_UNPROCESSABLE_ENTITY,
                    'VALIDATION_ERROR'
                );
            }
            
            $statusGroup = $this->statusGroupService->updateGroup($id, $validator->validated());
            Log::info("Status group {$id} updated successfully.");

            return $this->responseService->updated(
                new StatusGroupResource($statusGroup),
                'Status group updated successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->statusGroupService->deleteGroup($id);
            Log::info("Status group {$id} soft-deleted successfully.");
            
            return $this->responseService->deleted('Status group deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q');
            if (!$query) {
                return $this->responseService->error(
                    'Query parameter "q" is required.',
                    null,
                    ResponseService::HTTP_BAD_REQUEST,
                    'MISSING_PARAMETER'
                );
            }

            $results = $this->statusGroupService->searchGroups($query, 15);
            return $this->responseService->success(
                StatusGroupResource::collection($results),
                'Search results retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->statusGroupService->getStatistics();
            return $this->responseService->success(
                $stats,
                'Status group statistics retrieved successfully'
            );
        } catch (Throwable $e) {
            Log::error('Error getting statistics: ' . $e->getMessage());
            return $this->responseService->handleException($e);
        }
    }
}