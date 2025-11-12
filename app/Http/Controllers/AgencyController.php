<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Services\AgencyService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AgencyController extends Controller
{
    use ValidatesRequests;

    /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    /**
     * @var AgencyService
     */
    protected AgencyService $agencyService;

    /**
     * Create a new BrandController instance.
     *
     * @param ResponseService $responseService
     * @param AgencyService $agencyService
     */
    public function __construct(ResponseService $responseService, AgencyService $agencyService)
    {
        $this->responseService = $responseService;
        $this->agencyService = $agencyService;
    }

    /**
     * Get all agencies
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $agencies = Agency::with(['agencyGroup', 'agencyType', 'brand'])
                                ->where('status', '1')
                                ->get();

            return $this->responseService->success($agencies, 'Agencies retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Create a new agency
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|array',
                'name.*' => 'string|max:255|distinct|unique:agency,name',
                'type' => 'required|array', 
                'type.*' => 'integer|exists:agency_type,id', 
                'client'  => 'required|array',
                'client.*.*' => 'integer|exists:brands,id'
            ];

            $validatedData = $this->validate($request, $rules);
           
           
            $agency = $this->agencyService->create($validatedData);

            return $this->responseService->created(
                new AgencyResource($agency),
                'Agency created successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get a specific agency
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $agency = Agency::with(['agencyGroup', 'agencyType', 'brand'])->findOrFail($id);
            return $this->responseService->success($agency, 'Agency retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update an agency
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $agency = Agency::findOrFail($id);
            
            $rules = [
                'name' => 'sometimes|required|string|max:255|unique:agency,name,' . $id,
                'agency_group_id' => 'nullable|integer|exists:agency_groups,id', 
                'agency_type_id' => 'sometimes|required|integer|exists:agency_type,id', 
                'brand_id' => 'nullable|integer|exists:brands,id', 
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);
            
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }
            
            if (empty($validatedData)) {
                return $this->responseService->error('No data provided for update', null, 400, 'NO_DATA');
            }

            $agency->update($validatedData);
            $agency->load(['agencyGroup', 'agencyType', 'brand']);

            return $this->responseService->updated($agency, 'Agency updated successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Delete an agency
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $agency = Agency::findOrFail($id);
            $agency->delete(); // Soft Delete

            return $this->responseService->deleted('Agency deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}