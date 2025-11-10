<?php

namespace App\Http\Controllers;

use App\Services\StateService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\StateResource;

class StateController extends Controller
{
    use ValidatesRequests;

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
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'country_id' => 'required|integer|exists:countries,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $state = $this->stateService->createState($validatedData);
            $data = new StateResource($state);
            return $this->responseService->created($data, 'State created successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Ek specific state dikhayein
     */
    public function show(int $id): JsonResponse
    {
        try {
            $state = $this->stateService->getStateById($id);
            $data = new StateResource($state);
            return $this->responseService->success($data, 'State retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * State update karein
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'country_id' => 'required|integer|exists:countries,id',
            ];

            $validatedData = $this->validate($request, $rules);

            $state = $this->stateService->updateState($id, $validatedData);
            $data = new StateResource($state);
            return $this->responseService->updated($data, 'State updated successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * State delete karein (HARD delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->stateService->deleteState($id);
            return $this->responseService->deleted('State deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}

