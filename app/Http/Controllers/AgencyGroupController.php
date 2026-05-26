<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Services\AgencyGroupService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AgencyGroupController extends Controller
{
    use ValidatesRequests;

    protected ResponseService $responseService;
    protected AgencyGroupService $agencyGroupService;

    public function __construct(ResponseService $responseService, AgencyGroupService $agencyGroupService)
    {
        $this->responseService = $responseService;
        $this->agencyGroupService = $agencyGroupService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
            ]);

            $perPage = (int) $request->input('per_page', 15);
            $searchTerm = $request->input('search');

            $groups = $this->agencyGroupService->getAllGroups($perPage, $searchTerm);

            return $this->responseService->paginated(
                AgencyResource::collection($groups),
                'Agency groups retrieved successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $group = $this->agencyGroupService->getGroup($id);

            if (!$group) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->success(
                new AgencyResource($group),
                'Agency group retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validate($request, [
                'name' => 'required|string|max:255',
                'agency_type' => 'nullable|integer|exists:agency_type,id',
                'status' => 'nullable|in:1,2,15',
            ]);

            $validatedData['status'] = $validatedData['status'] ?? '1';

            $group = $this->agencyGroupService->createGroup($validatedData);

            return $this->responseService->created(
                new AgencyResource($group),
                'Agency group created successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $this->validate($request, [
                'name' => 'sometimes|required|string|max:255',
                'agency_type' => 'sometimes|nullable|integer|exists:agency_type,id',
                'status' => 'sometimes|required|in:1,2,15',
            ]);

            $group = $this->agencyGroupService->updateGroup($id, $validatedData);

            return $this->responseService->updated(
                new AgencyResource($group),
                'Agency group updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->agencyGroupService->deleteGroup($id);

            return $this->responseService->deleted('Agency group deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
