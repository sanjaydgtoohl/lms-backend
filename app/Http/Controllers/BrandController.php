<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Services\ResponseService; // <-- 1. Import your service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable; // <-- 2. Import Throwable for catching all errors

class BrandController extends Controller
{
    /**
     * @var ResponseService
     */
    protected $responseService; // <-- 3. Create a property for the service

    /**
     * Inject the ResponseService.
     *
     * @param ResponseService $responseService
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService; // <-- 4. Inject the service
    }

    /**
     * Display a listing of the brands.
     * GET /brands
     */
    public function index(Request $request)
    {
        try {
            $brands = Brand::with([
                'agency', 'brandType', 'contactPerson', 'industry',
                'country', 'state', 'city', 'region', 'subregions'
            ])
            ->where('status', '1')
            ->paginate($request->input('per_page', 15));

            // Use your service's 'paginated' method.
            // Your service will automatically handle the pagination meta.
            return $this->responseService->paginated($brands, 'Brands retrieved successfully');

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created brand in storage.
     * POST /brands
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'brand_type_id' => 'required|integer|exists:brand_types,id',
                'industry_id' => 'required|integer|exists:industries,id',
                'country_id' => 'required|integer|exists:countries,id',
                'website' => 'nullable|url|max:255',
                'postal_code' => 'nullable|string|max:20',
                'state_id' => 'nullable|integer|exists:states,id',
                'city_id' => 'nullable|integer|exists:cities,id',
                'region_id' => 'nullable|integer|exists:regions,id',
                'subregions_id' => 'nullable|integer|exists:subregions,id',
                'contact_person_id' => 'nullable|integer|exists:users,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
            ]);

            // This line will automatically throw a ValidationException
            // if validation fails, which handleException will catch.
            $validatedData = $validator->validate();

            // Add the non-validated data
            $validatedData['slug'] = Str::slug($request->name) . '-' . uniqid();
            $validatedData['created_by'] = Auth::id();
            $validatedData['status'] = '1';

            $brand = Brand::create($validatedData);

            // Use your service's 'created' method
            return $this->responseService->created($brand, 'Brand created successfully');

        } catch (Throwable $e) {
            // This will catch ValidationException, DB errors, etc.
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Display the specified brand.
     * GET /brands/{id}
     */
    public function show($id)
    {
        try {
            $brand = Brand::with([
                'agency', 'brandType', 'contactPerson', 'industry',
                'country', 'state', 'city', 'region', 'subregions'
            ])->findOrFail($id); // This throws ModelNotFoundException if not found

            // Use your service's 'success' method
            return $this->responseService->success($brand, 'Brand retrieved successfully');

        } catch (Throwable $e) {
            // Your handleException method will catch ModelNotFoundException
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update the specified brand in storage.
     * PUT /brands/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // 1. Find the brand first
            $brand = Brand::findOrFail($id);

            // 2. Define validation rules (use 'sometimes' for updates)
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'brand_type_id' => 'sometimes|required|integer|exists:brand_types,id',
                'industry_id' => 'sometimes|required|integer|exists:industries,id',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'website' => 'sometimes|nullable|url|max:255',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'state_id' => 'sometimes|nullable|integer|exists:states,id',
                'city_id' => 'sometimes|nullable|integer|exists:cities,id',
                'region_id' => 'sometimes|nullable|integer|exists:regions,id',
                'subregions_id' => 'sometimes|nullable|integer|exists:subregions,id',
                'contact_person_id' => 'sometimes|nullable|integer|exists:users,id',
                'agency_id' => 'sometimes|nullable|integer|exists:agency,id',
                'status' => 'sometimes|required|in:1,2,15',
            ]);

            // 3. Validate the data
            $validatedData = $validator->validate();

            // 4. Handle slug update if name changed
            if ($request->has('name')) {
                $validatedData['slug'] = Str::slug($request->name) . '-' . $brand->id; // Use ID for uniqueness
            }

            // 5. Update the brand
            $brand->update($validatedData);

            // Use your service's 'updated' method
            return $this->responseService->updated($brand, 'Brand updated successfully');

        } catch (Throwable $e) {
            // Catches ModelNotFoundException, ValidationException, etc.
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove the specified brand from storage (Soft Delete).
     * DELETE /brands/{id}
     */
    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->delete();

            // --- THIS IS THE CHANGE YOU REQUESTED ---
            // Send a 200 OK with a JSON body
            return $this->responseService->deleted('Brand deleted successfully');

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}

