<?php

namespace App\Http\Controllers;

use App\Models\Agency;
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
     * The response service instance
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Constructor
     *
     * @param ResponseService $responseService
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
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
                'name' => 'required|string|max:255|unique:agency,name',
                'agency_group_id' => 'nullable|integer|exists:agency_groups,id', 
                'agency_type_id' => 'required|integer|exists:agency_type,id', 
                'brand_id' => 'nullable|integer|exists:brands,id', 
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['status'] = $validatedData['status'] ?? '1';

            $agency = Agency::create($validatedData);
            $agency->load(['agencyGroup', 'agencyType', 'brand']); 

            return $this->responseService->created($agency, 'Agency created successfully');
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