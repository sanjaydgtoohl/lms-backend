<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use App\Services\ResponseService;
use App\Http\Resources\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    protected $countryService;
    protected $responseService;

    public function __construct(CountryService $countryService, ResponseService $responseService)
    {
        $this->countryService = $countryService;
        $this->responseService = $responseService;
    }

    /**
     * Paginated list laayein (e.g., /api/v1/countries)
     */
    public function index(): JsonResponse
    {
        try {
            $countries = $this->countryService->getPaginatedCountries();
            // Transform paginator items with CountryResource while keeping pagination meta
            $countries->getCollection()->transform(function ($country) {
                return new CountryResource($country);
            });
            return $this->responseService->paginated($countries, 'Countries retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Saari countries ki list laayein (e.g., /api/v1/countries/all)
     * Yeh dropdowns ke liye achha hai.
     */
    public function getAll(): JsonResponse
    {
        try {
            $countries = $this->countryService->getAllCountries();
            return $this->responseService->success(CountryResource::collection($countries), 'All countries retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }


    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:countries,name',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $country = $this->countryService->createCountry($validator->validated());
            return $this->responseService->created(new CountryResource($country), 'Country created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific country dikhayein
     */
    public function show($id): JsonResponse
    {
        try {
            $country = $this->countryService->getCountryById($id);
            return $this->responseService->success(new CountryResource($country), 'Country retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Country update karein
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Status field nahi hai, isliye use validate nahi karein
            'name' => 'required|string|max:255|unique:countries,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $country = $this->countryService->updateCountry($id, $validator->validated());
            return $this->responseService->updated(new CountryResource($country), 'Country updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Country delete karein (HARD delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->countryService->deleteCountry($id);
            return $this->responseService->deleted('Country deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}

