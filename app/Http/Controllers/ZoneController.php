<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Services\ZoneService;       
use App\Services\ResponseService;  
use App\Http\Resources\ZoneResource; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ZoneController extends Controller
{
    protected $zoneService;
    protected $responseService; // Response service
    public function __construct(ZoneService $zoneService, ResponseService $responseService)
    {
        $this->zoneService = $zoneService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
            'per_page' => 'nullable|integer|min:1',
            'search'   => 'nullable|string|max:100' // <-- Search validation
        ]);
            $perPage = (int) $request->query('per_page', 10);
            if ($perPage <= 0) { $perPage = 10; }
            $searchTerm = $request->query('search', null);
            $zones = $this->zoneService->getAllZones($perPage, $searchTerm);
            $data = ZoneResource::collection($zones);
            
            // Service ka 'paginated' method istemaal karein
            return $this->responseService->paginated($data, 'Zones retrieved successfully.');
        
        } catch (ValidationException $e) { // <-- Validation errors ke liye
            return $this->responseService->validationError($e->errors());
        }   catch (Throwable $e) {
            // Error ko response service ke handler ko bhej dein
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get all active zones (non-paginated) for dropdowns
     */
    public function getAll(): JsonResponse
    {
        try {
            $zones = $this->zoneService->getActiveZonesList();
            $data = ZoneResource::collection($zones);
            return $this->responseService->success($data, 'Zones list retrieved successfully.');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // 1. Validation
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:zones',
                'status' => 'required|in:1,2,15',
            ]);

            // 2. Logic ko Service ko pass karein
            $zone = $this->zoneService->createZone($validatedData);
            $data = new ZoneResource($zone);

            // 3. Service ka 'created' method istemaal karein
            return $this->responseService->created($data, 'Zone created successfully.');

        } catch (Throwable $e) {
            // ValidationException ho ya koi aur, handler sambhaal lega
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        // Route-model binding yahaan 'ModelNotFoundException' ko 
        // pehle hi handle kar lega, jo 'handleException' mein covered hai.
        try {
            $zone = $this->zoneService->getZoneById($id);
            $data = new ZoneResource($zone);
            return $this->responseService->success($data, 'Zone retrieved successfully.');
        
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $zone = $this->zoneService->getZoneById($id);
            // 1. Validation
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:zones,name,' . $zone->id,
                'status' => 'required|in:1,2,15',
            ]);

            // 2. Logic ko Service ko pass karein
            $this->zoneService->updateZone($zone, $validatedData);
            $data = new ZoneResource($zone->fresh()); // fresh() se updated data load hoga

            // 3. Service ka 'updated' method istemaal karein
            return $this->responseService->updated($data, 'Zone updated successfully.');

        } catch (Throwable $e) {
            // ValidationException ho ya koi aur, handler sambhaal lega
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $zone = $this->zoneService->getZoneById($id);
            $this->zoneService->deleteZone($zone);
            
            // Service ka 'deleted' method istemaal karein
            return $this->responseService->deleted('Zone deleted successfully.');

        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}