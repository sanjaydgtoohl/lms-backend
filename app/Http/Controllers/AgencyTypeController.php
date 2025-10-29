<?php

namespace App\Http\Controllers;

use App\Services\AgencyTypeService; // Import Service
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;

class AgencyTypeController extends Controller
{
    protected $responseService;
    protected $agencyTypeService; // Inject Service

    public function __construct(ResponseService $responseService, AgencyTypeService $agencyTypeService)
    {
        $this->responseService = $responseService;
        $this->agencyTypeService = $agencyTypeService;
    }

    public function index(): JsonResponse
    {
        try {
            $types = $this->agencyTypeService->getTypes();
            return $this->responseService->success($types, 'Agency types retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agency_types,name', // Fix: agency_type -> agency_types
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }
        
        try {
            $agencyType = $this->agencyTypeService->createType($validator->validated());
            return $this->responseService->created($agencyType, 'Agency type created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $agencyType = $this->agencyTypeService->getTypeById($id);
            return $this->responseService->success($agencyType, 'Agency type retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Service/Repo findOrFail karega, isliye $id ko unique rule mein seedha use kar sakte hain
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agency_types,name,' . $id, // Fix: agency_type -> agency_types
            'status' => 'required|in:1,2,15',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }
        
        try {
            $agencyType = $this->agencyTypeService->updateType($id, $validator->validated());
            return $this->responseService->updated($agencyType, 'Agency type updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->agencyTypeService->deleteType($id);
            return $this->responseService->deleted('Agency type deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}