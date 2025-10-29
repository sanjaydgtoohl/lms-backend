<?php

namespace App\Http\Controllers;

use App\Services\StateService;
use App\Services\ResponseService; // Maan rahe hain ki yeh service maujood hai
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StateResource; // Naya Resource import karein

class StateController extends Controller
{
    protected $stateService;
    protected $responseService;

    public function __construct(StateService $stateService, ResponseService $responseService)
    {
        $this->stateService = $stateService;
        $this->responseService = $responseService;
    }

    /**
     * Paginated list laayein (e.g., /api/v1/states)
     */
    public function index(): JsonResponse
    {
        try {
            $states = $this->stateService->getPaginatedStates();
            // Transform paginator items while preserving pagination meta
            $states->getCollection()->transform(function ($state) {
                return new StateResource($state);
            });
            return $this->responseService->paginated($states, 'States retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Saare states ki list laayein (e.g., /api/v1/states/all)
     */
    public function getAll(): JsonResponse
    {
        try {
            $states = $this->stateService->getAllStates();
            return $this->responseService->success(StateResource::collection($states), 'All states retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific country ke saare states laayein
     * (e.g., /api/v1/countries/1/states)
     */
    public function getStatesByCountry($countryId): JsonResponse
    {
        try {
            $states = $this->stateService->getStatesByCountry($countryId);
            // Resource collection ka istemal karein
            $data = StateResource::collection($states);
            return $this->responseService->success($data, 'States for country retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Naya state store karein
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id', // Validate karein ki country maujood hai
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $state = $this->stateService->createState($validator->validated());
            // Naye resource ka istemal karein
            $data = new StateResource($state);
            return $this->responseService->created($data, 'State created successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific state dikhayein
     */
    public function show($id): JsonResponse
    {
        try {
            $state = $this->stateService->getStateById($id);
            // Resource ka istemal karein
            $data = new StateResource($state);
            return $this->responseService->success($data, 'State retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * State update karein
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id', // Validate karein ki country maujood hai
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray(), 'Validation failed');
        }

        try {
            $state = $this->stateService->updateState($id, $validator->validated());
            // Resource ka istemal karein
            $data = new StateResource($state);
            return $this->responseService->updated($data, 'State updated successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * State delete karein (HARD delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->stateService->deleteState($id);
            return $this->responseService->deleted('State deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}

