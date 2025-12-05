<?php

namespace App\Http\Controllers;

use App\Http\Resources\BriefResource;
use App\Models\Brief;
use App\Services\BriefService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

class BriefController extends Controller
{
    use ValidatesRequests;

    /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    /**
     * @var BriefService
     */
    protected BriefService $briefService;

    /**
     * Create a new BriefController instance.
     *
     * @param ResponseService $responseService
     * @param BriefService $briefService
     */
    public function __construct(ResponseService $responseService, BriefService $briefService)
    {
        $this->responseService = $responseService;
        $this->briefService = $briefService;
    }

    /**
     * Display a listing of the briefs.
     *
     * GET /briefs
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
                'brand_id' => 'nullable|integer|exists:brands,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
                'assign_user_id' => 'nullable|integer|exists:users,id',
                'brief_status_id' => 'nullable|integer|exists:brief_statuses,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'status' => 'nullable|in:1,2,15',
            ]);

            $perPage = (int) $request->input('per_page', 15);
            $searchTerm = $request->input('search', null);

            // If filters are provided, use the filter method
            if ($request->anyFilled(['brand_id', 'agency_id', 'assign_user_id', 'brief_status_id', 'priority_id', 'status'])) {
                $filters = array_filter([
                    'brand_id' => $request->input('brand_id'),
                    'agency_id' => $request->input('agency_id'),
                    'assign_user_id' => $request->input('assign_user_id'),
                    'brief_status_id' => $request->input('brief_status_id'),
                    'priority_id' => $request->input('priority_id'),
                    'status' => $request->input('status'),
                    'search' => $searchTerm,
                ], fn($value) => $value !== null && $value !== '');
                $briefs = $this->briefService->getBriefsByFilters($filters, $perPage);
            } else {
                $briefs = $this->briefService->getAllBriefs($perPage, $searchTerm);
            }

            return $this->responseService->paginated(
                BriefResource::collection($briefs),
                'Briefs retrieved successfully'
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
     * Display the specified brief.
     *
     * GET /briefs/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $brief = $this->briefService->getBrief($id);

            if (!$brief) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->success(
                new BriefResource($brief),
                'Brief retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created brief in storage.
     *
     * POST /briefs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'product_name' => 'nullable|string|max:255',
                'contact_person_id' => 'required|integer|exists:leads,id',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
                'mode_of_campaign' => 'nullable|in:programmatic,non_programmatic',
                'media_type' => 'nullable|string|max:255',
                'budget' => 'nullable|numeric|min:0',
                'assign_user_id' => 'nullable|integer|exists:users,id',
                'brief_status_id' => 'nullable|integer|exists:brief_statuses,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'comment' => 'nullable|string',
                'submission_date' => 'nullable|date_format:Y-m-d H:i:s',
                'status' => 'nullable|in:1,2,15',
            ];

            $this->validate($request, $rules);

            // Validate campaign mode and media_type combination
            if ($request->has('mode_of_campaign') && $request->has('media_type')) {
                $mode = $request->input('mode_of_campaign');
                $mediaType = $request->input('media_type');
                $validTypes = Brief::getCampaignTypesByMode($mode);
                
                if (!in_array($mediaType, $validTypes)) {
                    return $this->responseService->validationError(
                        ['media_type' => ["Invalid media type for mode '{$mode}'. Valid types are: " . implode(', ', array_map('strtoupper', $validTypes))]],
                        'Validation failed'
                    );
                }
            }

            $data = $request->all();
            $data['uuid'] = (string) Str::uuid();
            $data['slug'] = Str::slug($request->input('name')) . '-' . uniqid();
            $data['created_by'] = Auth::id();
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = '2';
            }

            $brief = $this->briefService->createBrief($data);

            return $this->responseService->success(
                new BriefResource($brief),
                'Brief created successfully',
                201
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
     * Update the specified brief in storage.
     *
     * PUT /briefs/{id}
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
                'product_name' => 'nullable|string|max:255',
                'contact_person_id' => 'sometimes|required|integer|exists:leads,id',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
                'mode_of_campaign' => 'nullable|in:programmatic,non_programmatic',
                'media_type' => 'nullable|string|max:255',
                'budget' => 'nullable|numeric|min:0',
                'assign_user_id' => 'nullable|integer|exists:users,id',
                'brief_status_id' => 'nullable|integer|exists:brief_statuses,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'comment' => 'nullable|string',
                'submission_date' => 'nullable|date_format:Y-m-d H:i:s',
                'status' => 'nullable|in:1,2,15',
            ];

            $this->validate($request, $rules);

            // Validate campaign mode and media_type combination
            if ($request->has('mode_of_campaign') && $request->has('media_type')) {
                $mode = $request->input('mode_of_campaign');
                $mediaType = $request->input('media_type');
                $validTypes = Brief::getCampaignTypesByMode($mode);
                
                if (!in_array($mediaType, $validTypes)) {
                    return $this->responseService->validationError(
                        ['media_type' => ["Invalid media type for mode '{$mode}'. Valid types are: " . implode(', ', array_map('strtoupper', $validTypes))]],
                        'Validation failed'
                    );
                }
            }

            $brief = $this->briefService->updateBrief($id, $request->all());

            if (!$brief) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->success(
                new BriefResource($brief),
                'Brief updated successfully'
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
     * Remove the specified brief from storage.
     *
     * DELETE /briefs/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->briefService->deleteBrief($id);

            if (!$deleted) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->success(
                null,
                'Brief deleted successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get briefs by brand.
     *
     * GET /briefs/brand/{brandId}
     *
     * @param int $brandId
     * @param Request $request
     * @return JsonResponse
     */
    public function getByBrand(int $brandId, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $briefs = $this->briefService->getBriefsByBrand($brandId, $perPage);

            return $this->responseService->paginated(
                BriefResource::collection($briefs),
                'Briefs retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get briefs by agency.
     *
     * GET /briefs/agency/{agencyId}
     *
     * @param int $agencyId
     * @param Request $request
     * @return JsonResponse
     */
    public function getByAgency(int $agencyId, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $briefs = $this->briefService->getBriefsByAgency($agencyId, $perPage);

            return $this->responseService->paginated(
                BriefResource::collection($briefs),
                'Briefs retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get briefs by assigned user.
     *
     * GET /briefs/user/{userId}
     *
     * @param int $userId
     * @param Request $request
     * @return JsonResponse
     */
    public function getByAssignedUser(int $userId, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $briefs = $this->briefService->getBriefsByAssignedUser($userId, $perPage);

            return $this->responseService->paginated(
                BriefResource::collection($briefs),
                'Briefs retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update brief status.
     *
     * PUT /briefs/{id}/update-status
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $rules = [
                'brief_status_id' => 'required|integer|exists:brief_statuses,id',
            ];

            $this->validate($request, $rules);

            $brief = $this->briefService->getBrief($id);

            if (!$brief) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            $brief = $this->briefService->updateBrief($id, [
                'brief_status_id' => $request->input('brief_status_id'),
            ]);

            return $this->responseService->success(
                new BriefResource($brief),
                'Brief status updated successfully'
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
}
