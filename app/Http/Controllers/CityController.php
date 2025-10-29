<?php

namespace App\Http\Controllers;

use App\Services\CityService;
use App\Services\ResponseService; // Maan rahe hain ki yeh service maujood hai
use App\Http\Resources\CityResource; // Naya Resource import karein
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    protected $cityService;
    protected $responseService;

    public function __construct(CityService $cityService, ResponseService $responseService)
    {
        $this->cityService = $cityService;
        $this->responseService = $responseService;
    }

    /**
     * Paginated list laayein (e.g., /api/v1/cities)
     */
    public function index(): JsonResponse
    {
        try {
            $cities = $this->cityService->getPaginatedCities();
            // Resource collection ka istemal karein
            $data = CityResource::collection($cities);
            return $this->responseService->paginated($data, 'Cities retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Saare cities ki list laayein (e.g., /api/v1/cities/all)
     */
    public function getAll(): JsonResponse
    {
        try {
            $cities = $this->cityService->getAllCities();
            $data = CityResource::collection($cities);
            return $this->responseService->success($data, 'All cities retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific state ke saare cities laayein
     * (e.g., /api/v1/states/1/cities)
     */
    public function getCitiesByState($stateId): JsonResponse
    {
        try {
            $cities = $this->cityService->getCitiesByState($stateId);
            $data = CityResource::collection($cities);
            return $this->responseService->success($data, 'Cities for state retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific country ke saare cities laayein
     * (e.g., /api/v1/countries/1/cities)
     */
    public function getCitiesByCountry($countryId): JsonResponse
    {
        try {
            $cities = $this->cityService->getCitiesByCountry($countryId);
            $data = CityResource::collection($cities);
            return $this->responseService->success($data, 'Cities for country retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Nayi city store karein
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|integer|exists:countries,id',
            'state_id' => 'required|integer|exists:states,id',
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique rule: Naam state_id ke context mein unique hona chahiye
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('state_id', $request->state_id);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $city = $this->cityService->createCity($validator->validated());
            $data = new CityResource($city->load(['country', 'state'])); // Relations load karein
            return $this->responseService->created($data, 'City created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific city dikhayein
     */
    public function show($id): JsonResponse
    {
        try {
            $city = $this->cityService->getCityById($id);
            $data = new CityResource($city); // Repository pehle hi relations load kar chuka hai
            return $this->responseService->success($data, 'City retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * City update karein
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|integer|exists:countries,id',
            'state_id' => 'required|integer|exists:states,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('state_id', $request->state_id);
                })->ignore($id), // Update ke waqt current ID ignore karein
            ],
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $city = $this->cityService->updateCity($id, $validator->validated());
            $data = new CityResource($city->load(['country', 'state'])); // Relations reload karein
            return $this->responseService->updated($data, 'City updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->cityService->deleteCity($id);
            return $this->responseService->deleted('City deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
