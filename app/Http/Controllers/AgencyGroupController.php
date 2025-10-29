<?php

namespace App\Http\Controllers;

use App\Services\AgencyGroupService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;

class AgencyGroupController extends Controller
{
    protected $responseService;
    protected $agencyGroupService;

    public function __construct(ResponseService $responseService, AgencyGroupService $agencyGroupService)
    {
        $this->responseService = $responseService;
        $this->agencyGroupService = $agencyGroupService;
    }

    public function index(): JsonResponse
    {
        try {
            $groups = $this->agencyGroupService->getAll();
            return $this->responseService->success($groups, 'Agency groups retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agency_groups,name',
            'status' => 'nullable|in:1,2,15',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }
        
        try {
            $group = $this->agencyGroupService->create($validator->validated());
            return $this->responseService->created($group, 'Agency group created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $group = $this->agencyGroupService->getById((int)$id);
            return $this->responseService->success($group, 'Agency group retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agency_groups,name,' . $id,
            'status' => 'required|in:1,2,15',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }
        
        try {
            $group = $this->agencyGroupService->update((int)$id, $validator->validated());
            return $this->responseService->updated($group, 'Agency group updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->agencyGroupService->delete((int)$id);
            return $this->responseService->deleted('Agency group deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}