<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Models\BrandAgencyRelationship;
use App\Services\AgencyService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
            ]);

            $perPage = $request->input('per_page', 15);
            $searchTerm = $request->input('search', null);
            
            $query = Agency::with(['agencyType', 'brand', 'parentAgency'])
                            ->where('status', '1');

            // Apply search filter if search term is provided
            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('agencyType', function ($agencyTypeQuery) use ($searchTerm) {
                          $agencyTypeQuery->where('name', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            $agencies = $query->orderBy('created_at', 'desc')
                              ->paginate($perPage)
                              ->appends(request()->query());

            // Apply resource collection to paginated results
            $resource = AgencyResource::collection($agencies);

            return $this->responseService->paginated($resource, 'Agencies retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of agencies with only id and name (e.g., /api/v1/agencies/list)
     */
    public function list(): JsonResponse
    {
        try {
            $agencies = Agency::where('status', '1')
                              ->orderBy('id', 'desc')
                            //   ->limit(10000)
                              ->get()
                              ->map(function ($agency) {
                                  return [
                                      'id' => $agency->id,
                                      'name' => $agency->name,
                                  ];
                              });
            return $this->responseService->success($agencies, 'Agencies list retrieved');
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
                'name.*' => 'string|max:255|distinct|unique:agency,name,NULL,id,deleted_at,NULL',
                'type' => 'required|array', 
                'type.*' => 'integer|exists:agency_type,id', 
                'client'  => 'required|array',
                'client.*.*' => 'integer|exists:brands,id'
            ];

            $validatedData = $this->validate($request, $rules);
           
           
            $agency = $this->agencyService->create($validatedData);
            
            // Eager load relationships before returning the resource
            if ($agency) {
                $agency->load(['agencyType', 'brand', 'parentAgency']);
            }

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
            // $agency = Agency::with(['agencyType', 'brand', 'parentAgency'])->findOrFail($id);
            $agency = $this->agencyService->getById($id);
            return $this->responseService->success(new AgencyResource($agency), 'Agency retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update an existing agency
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Verify agency exists
            $agency = Agency::findOrFail($id);

            $rules = [
                'name' => 'sometimes|required|array',
                'name.*' => 'string|max:255|distinct|unique:agency,name,NULL,' . $id . ',deleted_at,NULL',
                'type' => 'sometimes|required|array', 
                'type.*' => 'integer|exists:agency_type,id', 
                'client' => 'sometimes|required|array',
                'client.*.*' => 'integer|exists:brands,id'
            ];

            $validatedData = $this->validate($request, $rules);
            
            // Use AgencyService to handle update with relationships
            $agency = $this->agencyService->update($id, $validatedData);
            
            // Eager load relationships before returning the resource
            if ($agency) {
                $agency->load(['agencyType', 'brand', 'parentAgency']);
            }

            return $this->responseService->success(
                new AgencyResource($agency),
                'Agency updated successfully'
            );
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

    /**
     * Get brands for a specific agency using only BrandAgencyRelationship table
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getBrands(int $id): JsonResponse
    {
        try {
            // Verify agency exists
            $agency = Agency::where('status', '1')->find($id);

            if (!$agency) {
                return $this->responseService->error('Agency not found', []);
            }

            // Get all brands for this agency from brand_agency_relationships table using relationship
            $brandRelationships = BrandAgencyRelationship::where('agency_id', $id)
                ->whereNull('deleted_at')
                ->with('brand:id,name')
                ->get();

            // Format brands data to return only id and name
            $brands = $brandRelationships->map(function ($relationship) {
                return [
                    'id' => $relationship->brand->id ?? null,
                    'name' => $relationship->brand->name ?? null,
                ];
            })->filter(function ($brand) {
                return $brand['id'] !== null; // Remove null entries
            })->values()->toArray();

            return $this->responseService->success([
                'agency_id' => $agency->id,
                'agency_name' => $agency->name,
                'brands' => $brands,
                'total_brands' => count($brands),
            ], 'Agency brands retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}