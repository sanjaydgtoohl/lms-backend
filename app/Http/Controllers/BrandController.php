<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Services\BrandService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class BrandController extends Controller
{
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
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
        } catch (\Illuminate\Validation\ValidationException $e) {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $brand = $this->brandService->getBrand((int) $id);

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
     * @return \Illuminate\Http\JsonResponse
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
                'zone_id' => 'nullable|integer|exists:zones,id',
                'agency_id' => 'nullable|integer|exists:agencies,id',
            ]);

            if ($validator->fails()) {
                return $this->responseService->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed'
                );
            }

            $validatedData = $validator->validated();

            // Add system-generated fields
            $validatedData['slug'] = Str::slug($request->name) . '-' . uniqid();
            $validatedData['created_by'] = Auth::id();
            $validatedData['status'] = '1';

            $brand = $this->brandService->createBrand($validatedData);

            return $this->responseService->created(
                new BrandResource($brand),
                'Brand created successfully'
            );
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'brand_type_id' => 'sometimes|required|integer|exists:brand_types,id',
                'industry_id' => 'sometimes|required|integer|exists:industries,id',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'website' => 'sometimes|nullable|url|max:255',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'state_id' => 'sometimes|nullable|integer|exists:states,id',
                'city_id' => 'sometimes|nullable|integer|exists:cities,id',
                'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
                'agency_id' => 'sometimes|nullable|integer|exists:agencies,id',
                'status' => 'sometimes|required|in:1,2,15',
            ]);

            if ($validator->fails()) {
                return $this->responseService->validationError(
                    $validator->errors()->toArray(),
                    'Validation failed'
                );
            }

            $validatedData = $validator->validated();

            // Update slug if name changed
            if ($request->has('name')) {
                $validatedData['slug'] = Str::slug($request->name) . '-' . $id;
            }

            $this->brandService->updateBrand((int) $id, $validatedData);

            // Fetch updated brand with relationships
            $brand = $this->brandService->getBrand((int) $id);

            if (!$brand) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->updated(
                new BrandResource($brand),
                'Brand updated successfully'
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $this->brandService->deleteBrand((int) $id);

            return $this->responseService->deleted('Brand deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function list()
    {
        try {
            // 2. Hum service se naya method call karenge
            $brandsList = $this->brandService->getBrandList();

            // 3. ResponseService se data ko success response me bhejenge
            return $this->responseService->success(
                $brandsList,
                'Brand list retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
