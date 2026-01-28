<?php

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

    /**
     * Display a listing of the miss campaigns.
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
                'lead_sub_source_id' => 'required|integer|exists:lead_sub_source,id',
                'image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:51200'
            ];

            $validatedData = $this->validate($request, $rules);

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
                'lead_sub_source_id' => 'sometimes|required|integer|exists:lead_sub_source,id',
                'image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:51200',
                'status' => 'sometimes|required|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

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