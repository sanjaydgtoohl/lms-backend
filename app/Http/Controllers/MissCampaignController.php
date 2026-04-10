<?php

/**
 * MissCampaign Controller
 * -----------------------------------------
 * Handles HTTP requests for miss campaign management, including CRUD operations and API responses.
 *
 * @package App\Http\Controllers
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Http\Controllers;

use App\Http\Resources\MissCampaignResource;
use App\Services\MissCampaignService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

class MissCampaignController extends Controller
{
    use ValidatesRequests;

    /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    /**
     * @var MissCampaignService
     */
    protected MissCampaignService $missCampaignService;

    /**
     * Create a new MissCampaignController instance.
     *
     * @param ResponseService $responseService
     * @param MissCampaignService $missCampaignService
     */
    public function __construct(ResponseService $responseService, MissCampaignService $missCampaignService)
    {
        $this->responseService = $responseService;
        $this->missCampaignService = $missCampaignService;
    }

    /**     * Validate location hierarchy consistency (country -> state -> city).
     *
     * @param array $data Validated data containing country_id, state_id, city_id
     * @return array|null Returns null on success, or validation error array on failure
     */
    protected function validateLocationHierarchy(array $data): ?array
    {
        $countryId = $data['country_id'];
        $stateId = $data['state_id'] ?? null;
        $cityId = $data['city_id'] ?? null;

        // Validate state belongs to country
        if (!empty($stateId)) {
            $state = \App\Models\State::find($stateId);
            if (!$state || (int) $state->country_id !== (int) $countryId) {
                return [
                    'state_id' => ['The selected state does not belong to the selected country.']
                ];
            }
        }

        // Validate city requires state and belongs to both state and country
        if (!empty($cityId)) {
            if (empty($stateId)) {
                return [
                    'city_id' => ['A state must be selected before selecting a city.']
                ];
            }

            $city = \App\Models\City::find($cityId);
            if (!$city || (int) $city->state_id !== (int) $stateId || (int) $city->country_id !== (int) $countryId) {
                return [
                    'city_id' => ['The selected city does not belong to the selected state and country.']
                ];
            }
        }

        return null; // No validation errors
    }

    /**     * Display a listing of the miss campaigns.
     *
     * GET /miss-campaigns
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
            $searchTerm = $request->input('search');

            $campaigns = $this->missCampaignService->getAllMissCampaigns($perPage, $searchTerm);

            return $this->responseService->paginated(
                MissCampaignResource::collection($campaigns),
                'Miss campaigns retrieved successfully'
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
     * Display the specified miss campaign.
     *
     * GET /miss-campaigns/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $campaign = $this->missCampaignService->getMissCampaign($id);

            if (!$campaign) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->success(
                new MissCampaignResource($campaign),
                'Miss campaign retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created miss campaign in storage.
     *
     * POST /miss-campaigns
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'brand_id' => 'required|integer|exists:brands,id',
                'lead_source_id' => 'required|integer|exists:lead_source,id',
                'lead_sub_source_id' => 'nullable|integer|exists:lead_sub_source,id',
                'media_type_id' => 'nullable|integer|exists:media_types,id',
                'industry_id' => 'nullable|integer|exists:industries,id',
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'nullable|integer|exists:states,id',
                'city_id' => 'nullable|integer|exists:cities,id',
                'image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:51200',
            ];

            $validatedData = $this->validate($request, $rules);

            // Validate location hierarchy consistency
            $locationErrors = $this->validateLocationHierarchy($validatedData);
            if ($locationErrors) {
                return $this->responseService->validationError($locationErrors, 'Validation failed');
            }

            // Add system-generated fields
            $validatedData['slug'] = Str::slug($request->name) . '-' . uniqid();
            $validatedData['status'] = '1'; // Default active status

            // Handle image upload if present
            if ($request->hasFile('image_path')) {
                $uploadResult = $this->missCampaignService->uploadImage($request->file('image_path'));
                $validatedData['image_path'] = $uploadResult['path'] ?? null;
            }

            $campaign = $this->missCampaignService->createMissCampaign($validatedData);

            return $this->responseService->created(
                new MissCampaignResource($campaign),
                'Miss campaign created successfully'
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
     * Update the specified miss campaign in storage.
     *
     * PUT /miss-campaigns/{id}
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
                'brand_id' => 'sometimes|required|integer|exists:brands,id',
                'lead_source_id' => 'sometimes|required|integer|exists:lead_source,id',
                'lead_sub_source_id' => 'sometimes|nullable|integer|exists:lead_sub_source,id',
                'media_type_id' => 'sometimes|nullable|integer|exists:media_types,id',
                'industry_id' => 'sometimes|nullable|integer|exists:industries,id',
                'country_id' => 'sometimes|required|integer|exists:countries,id',
                'state_id' => 'sometimes|nullable|integer|exists:states,id',
                'city_id' => 'sometimes|nullable|integer|exists:cities,id',
                'image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:51200',
                'status' => 'sometimes|required|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            $campaign = $this->missCampaignService->getMissCampaign($id);
            if (!$campaign) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            // Prepare location data for validation (merge validated data with existing campaign data)
            $locationData = [
                'country_id' => array_key_exists('country_id', $validatedData) ? $validatedData['country_id'] : $campaign->country_id,
                'state_id' => array_key_exists('state_id', $validatedData) ? $validatedData['state_id'] : $campaign->state_id,
                'city_id' => array_key_exists('city_id', $validatedData) ? $validatedData['city_id'] : $campaign->city_id,
            ];

            // Clear city_id when state_id is being set to null to prevent hierarchy validation errors
            if (array_key_exists('state_id', $validatedData) && $validatedData['state_id'] === null && !array_key_exists('city_id', $validatedData)) {
                $locationData['city_id'] = null;
                $validatedData['city_id'] = null;
            }

            // Validate location hierarchy consistency
            $locationErrors = $this->validateLocationHierarchy($locationData);
            if ($locationErrors) {
                return $this->responseService->validationError($locationErrors, 'Validation failed');
            }

            // Update slug if name changed
            if ($request->has('name')) {
                $validatedData['slug'] = Str::slug($request->name) . '-' . $id;
            }

            // Handle image upload if present
            if ($request->hasFile('image_path')) {
                $uploadResult = $this->missCampaignService->uploadImage($request->file('image_path'));
                $validatedData['image_path'] = $uploadResult['path'] ?? null;
            }

            $this->missCampaignService->updateMissCampaign($id, $validatedData);

            // Fetch updated campaign with relationships
            $campaign = $this->missCampaignService->getMissCampaign($id);

            if (!$campaign) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->updated(
                new MissCampaignResource($campaign),
                'Miss campaign updated successfully'
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
     * Remove the specified miss campaign from storage (Soft Delete).
     *
     * DELETE /miss-campaigns/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->missCampaignService->deleteMissCampaign($id);

            return $this->responseService->deleted('Miss campaign deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of miss campaigns (for dropdowns)
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        try {
            $campaignsList = $this->missCampaignService->getMissCampaignList();

            return $this->responseService->success(
                $campaignsList,
                'Miss campaign list retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}