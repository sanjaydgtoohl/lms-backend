<?php

namespace App\Http\Controllers;

use App\Services\BrandService;
use App\Services\ResponseService;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;
use DomainException;
use Illuminate\Database\QueryException;

class BrandController extends Controller
{
    protected $brandService;
    protected $responseService;

    public function __construct(BrandService $brandService, ResponseService $responseService)
    {
        $this->brandService = $brandService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of the brands.
     * GET /brands
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $brands = $this->brandService->getAllBrands($perPage);
            return $this->responseService->paginated(
                BrandResource::collection($brands),
                'Brands retrieved successfully'
            );
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 400, 'DOMAIN_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('An unexpected error occurred while fetching brands.', $e->getMessage());
        }
    }

    /**
     * Store a newly created brand in storage.
     * POST /brands
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $validator = Validator::make($data, [
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
                'agency_id' => 'nullable|integer|exists:agency,id',
            ]);

            if ($validator->fails()) {
                return $this->responseService->validationError($validator->errors()->toArray());
            }

            $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
            $data['created_by'] = Auth::id();
            $data['status'] = '1';

            $brand = $this->brandService->createBrand($data);
            return $this->responseService->created(new BrandResource($brand), 'Brand created successfully');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 409, 'DB_ERROR');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 409, 'DOMAIN_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('An unexpected error occurred while creating brand.', $e->getMessage());
        }
    }

    /**
     * Display the specified brand.
     * GET /brands/{id}
     */
    public function show($id)
    {
        try {
            $brand = $this->brandService->getBrand($id);
             return $this->responseService->success(new BrandResource($brand), 'Brand retrieved successfully');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Brand not found');
        }
    }

    /**
     * Update the specified brand in storage.
     * PUT /brands/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $validator = Validator::make($data, [
                'name' => 'sometimes|required|string|max:255',
                'brand_type_id' => 'sometimes|required|integer|exists:brand_types,id',
                'industry_id' => 'sometimes|required|integer|exists:industries,id',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'website' => 'sometimes|nullable|url|max:255',
                'postal_code' => 'sometimes|required|string|max:20',
                'state_id' => 'sometimes|required|integer|exists:states,id',
                'city_id' => 'sometimes|required|integer|exists:cities,id',
                'region_id' => 'sometimes|nullable|integer|exists:regions,id',
                'subregions_id' => 'sometimes|nullable|integer|exists:subregions,id',
                'contact_person_id' => 'sometimes|nullable|integer|exists:users,id',
                'agency_id' => 'sometimes|nullable|integer|exists:agency,id',
                'status' => 'sometimes|required|in:1,2,15',
            ]);

            if ($validator->fails()) {
                return $this->responseService->validationError($validator->errors()->toArray());
            }

            if (!empty($data['name'])) {
                $data['slug'] = Str::slug($data['name']) . '-' . (string) $id;
            }

            $brand = $this->brandService->updateBrand($id, $data);
            return $this->responseService->updated(new BrandResource($brand), 'Brand updated successfully');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 409, 'DB_ERROR');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 409, 'DOMAIN_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Brand not found');
        }
    }

    /**
     * Remove the specified brand from storage (Soft Delete).
     * DELETE /brands/{id}
     */
    public function destroy($id)
    {
        try {
            $this->brandService->deleteBrand($id);
            return $this->responseService->deleted('Brand deleted successfully');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Brand not found');
        }
    }
}