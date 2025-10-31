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
    public function index()
    {
        try {
            $brandTypes = $this->brandTypeService->getAll();
            
            // Format the collection using the Resource
            return $this->responseService->success(
                BrandTypeResource::collection($brandTypes), 
                'Brand types retrieved successfully'
            );

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
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
                'status' => 'required|in:1,2,15',
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
                'status' => 'sometimes|required|in:1,2,15',
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

