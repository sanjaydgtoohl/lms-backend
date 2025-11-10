<?php

namespace App\Http\Controllers;

// --- Use your new Service and Resource ---
use App\Services\BrandTypeService; 
use App\Http\Resources\BrandTypeResource;
// --- Use your existing Response Service ---
use App\Services\ResponseService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BrandTypeController extends Controller
{
    protected $responseService;
    protected $brandTypeService;

    /**
     * Inject the BrandTypeService and ResponseService.
     */
    public function __construct(BrandTypeService $brandTypeService, ResponseService $responseService)
    {
        $this->brandTypeService = $brandTypeService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of the brand types.
     */
    public function index(Request $request) // <-- Request $request add karein
    {
        try {
            // (Optional but recommended) Validate inputs
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1',
                'search'   => 'nullable|string|max:100'
            ]);

            // 1. Pagination aur Search parameters lein
            $perPage = $request->get('per_page', 10);
            $searchTerm = $request->get('search', null);

            // 2. Parameters ko service mein pass karein
            $brandTypes = $this->brandTypeService->getAll((int) $perPage, $searchTerm);
            
            // 3. Resource ko paginator ke saath wrap karein (metadata preserve hota hai)
            $resource = BrandTypeResource::collection($brandTypes);

            // 4. ->success() ki jagah ->paginated() ka istemal karein
            return $this->responseService->paginated(
                $resource,
                'Brand types retrieved successfully'
            );

        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors());
        } catch (Throwable $e) {
            return $this->responseService->handleException($e); // Aapka original exception handler
        }
    }

    /**
     * Store a newly created brand type in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:brand_types,name,NULL,id,deleted_at,NULL',
                'status' => 'null|in:1,2,15',
            ]);

            $validatedData = $validator->validate();

            // Call the service with validated data
            $brandType = $this->brandTypeService->create($validatedData);

            // Format the new model using the Resource
            return $this->responseService->created(
                new BrandTypeResource($brandType), 
                'Brand type created successfully'
            );

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Display the specified brand type.
     */
    public function show($id)
    {
        try {
            // Call the service
            $brandType = $this->brandTypeService->findById($id); 

            // Format the model using the Resource
            return $this->responseService->success(
                new BrandTypeResource($brandType), 
                'Brand type retrieved successfully'
            );

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update the specified brand type in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // We find it first to ensure it exists before validating
            $this->brandTypeService->findById($id);

            $validator = Validator::make($request->all(), [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('brand_types')->ignore($id)->whereNull('deleted_at'),
                ],
                'status' => 'sometimes|null|in:1,2,15',
            ]);

            $validatedData = $validator->validate();

            // Call the service
            $brandType = $this->brandTypeService->update($id, $validatedData);

            // Format the updated model
            return $this->responseService->updated(
                new BrandTypeResource($brandType), 
                'Brand type updated successfully'
            );

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove the specified brand type from storage (Soft Delete).
     */
    public function destroy($id)
    {
        try {
            // The service handles the safety check
            $this->brandTypeService->delete($id);

            return $this->responseService->deleted('Brand type deleted successfully');

        } catch (Throwable $e) {
            // This will catch the "Resource in use" exception from the service
            return $this->responseService->handleException($e);
        }
    }
}

