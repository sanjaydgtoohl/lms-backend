<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Services\BrandService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;
use DomainException;

class BrandController extends Controller
{
    use ValidatesRequests;

    /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    /**
     * @var BrandService
     */
    protected BrandService $brandService;

    /**
     * Create a new BrandController instance.
     *
     * @param ResponseService $responseService
     * @param BrandService $brandService
     */
    public function __construct(ResponseService $responseService, BrandService $brandService)
    {
        $this->responseService = $responseService;
        $this->brandService = $brandService;
    }

    /**
     * Display a listing of the brands.
     *
     * GET /brands
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

            $perPage = (int) $request->input('per_page', 15);
            $searchTerm = $request->input('search', null);

            $brands = $this->brandService->getAllBrands($perPage, $searchTerm);

            return $this->responseService->paginated(
                BrandResource::collection($brands),
                'Brands retrieved successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError(
                $e->errors(),
                'Validation failed'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Display the specified brand.
     *
     * GET /brands/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $brand = $this->brandService->getBrand($id);

            if (!$brand) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->success(
                new BrandResource($brand),
                'Brand retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created brand in storage.
     *
     * POST /brands
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|unique:brands,name|string|max:255',
                'brand_type_id' => 'required|integer|exists:brand_types,id',
                'industry_id' => 'required|integer|exists:industries,id',
                'country_id' => 'required|integer|exists:countries,id',
                'website' => 'nullable|url|max:255',
                'postal_code' => 'nullable|string|max:20',
                'state_id' => 'required|integer|exists:states,id',
                'city_id' => 'required|integer|exists:cities,id',
                'zone_id' => 'required|integer|exists:zones,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
            ];

            $validatedData = $this->validate($request, $rules);

            // Add system-generated fields
            $validatedData['slug'] = Str::slug($request->name);
            $validatedData['created_by'] = Auth::id();
            $validatedData['status'] = '1';

            $brand = $this->brandService->createBrand($validatedData);

            return $this->responseService->created(
                new BrandResource($brand),
                'Brand created successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update the specified brand in storage.
     *
     * PUT /brands/{id}
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $rules = [
                'name' => 'sometimes|required|string|max:255',
                'brand_type_id' => 'sometimes|required|integer|exists:brand_types,id',
                'industry_id' => 'sometimes|required|integer|exists:industries,id',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'website' => 'sometimes|nullable|url|max:255',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'state_id' => 'sometimes|required|integer|exists:states,id',
                'city_id' => 'sometimes|required|integer|exists:cities,id',
                'zone_id' => 'sometimes|required|integer|exists:zones,id',
                'agency_id' => 'sometimes|nullable|integer|exists:agency,id',
                'status' => 'sometimes|required|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            // Update slug if name changed
            if ($request->has('name')) {
                $validatedData['slug'] = Str::slug($request->name) . '-' . $id;
            }

            $this->brandService->updateBrand($id, $validatedData);

            // Fetch updated brand with relationships
            $brand = $this->brandService->getBrand($id);

            if (!$brand) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->updated(
                new BrandResource($brand),
                'Brand updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (DomainException $e) {
            return $this->responseService->validationError(
                ['brand' => [$e->getMessage()]],
                $e->getMessage()
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove the specified brand from storage (Soft Delete).
     *
     * DELETE /brands/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->brandService->deleteBrand($id);

            return $this->responseService->deleted('Brand deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of brands (for dropdowns)
     * If brand_id is provided, return the agency for that brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $brands = $this->brandService->getBrandList();
            return $this->responseService->success($brands, 'Brand list retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get agency for a specific brand
     *
     * GET /brands/{id}/agencies
     *
     * @param int $id
     * @return JsonResponse
     */
    public function agencies(int $id): JsonResponse
    {
        try {
            $brand = $this->brandService->getBrand($id);

            if (!$brand) {
                return $this->responseService->notFound('Brand not found');
            }

            $agency = Agency::where('id', $brand->agency_id)->select('id', 'name')->first();

            return $this->responseService->success(
                $agency,
                'Agency retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}