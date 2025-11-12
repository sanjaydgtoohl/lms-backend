<?php

namespace App\Http\Controllers;

use App\Models\AgencyGroup;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AgencyGroupController extends Controller
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
     * Get all agency groups
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $groups = AgencyGroup::where('status', '1')->get(['id', 'name', 'slug', 'status']);
            return $this->responseService->success($groups, 'Agency groups retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Create a new agency group
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255|unique:agency_groups,name',
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['status'] = $validatedData['status'] ?? '1';

            $agencyGroup = AgencyGroup::create($validatedData);

            return $this->responseService->created($agencyGroup, 'Agency group created successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get a specific agency group
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $group = AgencyGroup::findOrFail($id);
            return $this->responseService->success($group, 'Agency group retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update an agency group
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $group = AgencyGroup::findOrFail($id);

            $rules = [
                'name' => 'sometimes|required|string|max:255|unique:agency_groups,name,' . $id,
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }
            
            if (empty($validatedData)) {
                return $this->responseService->error('No data provided for update', null, 400, 'NO_DATA');
            }

            $group->update($validatedData);

            return $this->responseService->updated($group, 'Agency group updated successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Delete an agency group
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $group = AgencyGroup::findOrFail($id);
            $group->delete(); // Soft Delete

            return $this->responseService->deleted('Agency group deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}