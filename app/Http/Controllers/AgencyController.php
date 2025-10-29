<?php

namespace App\Http\Controllers;

use App\Services\AgencyService; // Import AgencyService
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;
// Import ValidationException taaki use catch kar sakein
use Illuminate\Validation\ValidationException;

class AgencyController extends Controller
{
    protected $responseService;
    protected $agencyService; // Service ko inject karein

    public function __construct(ResponseService $responseService, AgencyService $agencyService)
    {
        $this->responseService = $responseService;
        $this->agencyService = $agencyService;
    }

    public function index(): JsonResponse
    {
        try {
            $agencies = $this->agencyService->getAgencies();
            return $this->responseService->paginated($agencies, 'Agencies retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function create(): JsonResponse
    {
        try {
            $data = $this->agencyService->getCreateData();
            return $this->responseService->success($data, 'Data for create agency form retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'agency_group_id' => 'nullable|exists:agency_groups,id',
            'agency_type_id' => 'required|exists:agency_types,id', // Fix: agency_type -> agency_types
            'brand_id' => 'required|exists:brands,id',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $agency = $this->agencyService->createAgency($validator->validated());
            return $this->responseService->created($agency, 'Agency created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function storeBatch(Request $request): JsonResponse
    {
        $rootValidator = Validator::make($request->all(), [
            'agencies' => 'required|array',
            'agencies.*' => 'required|array'
        ]);

        if ($rootValidator->fails()) {
            return $this->responseService->validationError($rootValidator->errors()->toArray(), 'Validation failed: Expected a root "agencies" array.');
        }

        try {
            $createdAgencies = $this->agencyService->createAgencyBatch($request->input('agencies'));
            return $this->responseService->created($createdAgencies, 'All agencies created successfully');
        
        } catch (ValidationException $e) {
            // Service se throw ki gayi ValidationException ko yahan catch karein
             return $this->responseService->validationError($e->errors(), 'One or more agencies failed validation. No agencies were created.');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $agency = $this->agencyService->getAgencyById($id);
            return $this->responseService->success($agency, 'Agency retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'agency_group_id' => 'nullable|exists:agency_groups,id',
            'agency_type_id' => 'required|exists:agency_types,id', // Fix: agency_type -> agency_types
            'brand_id' => 'required|exists:brands,id',
            'status' => 'required|in:1,2,15',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            // Note: Service ab findOrFail ka dhyan khud rakhega repository ke through
            $agency = $this->agencyService->updateAgency($id, $validator->validated());
            return $this->responseService->updated($agency, 'Agency updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->agencyService->deleteAgency($id);
            return $this->responseService->deleted('Agency deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}