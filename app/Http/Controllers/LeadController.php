<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use App\Services\ResponseService;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    use ValidatesRequests;

    /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    /**
     * @var LeadService
     */
    protected LeadService $leadService;

    /**
     * Create a new LeadController instance.
     *
     * @param ResponseService $responseService
     * @param LeadService $leadService
     */
    public function __construct(ResponseService $responseService, LeadService $leadService)
    {
        $this->responseService = $responseService;
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of the leads.
     *
     * GET /leads
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
                'current_assign_user' => 'nullable|integer|exists:users,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'status' => 'nullable|in:1,2,15',
            ]);

            $perPage = (int) $request->input('per_page', 15);
            $searchTerm = $request->input('search', null);

            // If filters are provided, use the filter method
            if ($request->has(['brand_id', 'agency_id', 'current_assign_user', 'priority_id', 'status'])) {
                $filters = array_filter([
                    'brand_id' => $request->input('brand_id'),
                    'agency_id' => $request->input('agency_id'),
                    'current_assign_user' => $request->input('current_assign_user'),
                    'priority_id' => $request->input('priority_id'),
                    'status' => $request->input('status'),
                    'search' => $searchTerm,
                ]);
                $leads = $this->leadService->getLeadsWithFilters($filters, $perPage);
            } else {
                $leads = $this->leadService->getAllLeads($perPage, $searchTerm);
            }

            return $this->responseService->paginated(
                LeadResource::collection($leads),
                'Leads retrieved successfully'
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
     * Get lead activity listing.
     *
     * GET /leads/activity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activity(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
                'current_assign_user' => 'nullable|integer|exists:users,id',
                'lead_status_relation' => 'nullable|integer|exists:lead_statuses,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'status' => 'nullable|in:1,2,15',
            ]);

            $perPage = (int) $request->input('per_page', 5);
            $searchTerm = $request->input('search', null);

            // Get leads with required relations for activity
            $query = Lead::with([
                'brand',
                'assignedUser',
                'callStatusRelation',
                'leadStatusRelation',
                'priority'
            ])->accessibleToUser();

            // Apply filters if provided
            if ($request->has('brand_id') && $request->input('brand_id')) {
                $query->where('brand_id', $request->input('brand_id'));
            }

            if ($request->has('agency_id') && $request->input('agency_id')) {
                $query->where('agency_id', $request->input('agency_id'));
            }

            if ($request->has('current_assign_user') && $request->input('current_assign_user')) {
                $query->where('current_assign_user', $request->input('current_assign_user'));
            }

            if( $request->has('lead_status_relation') && $request->input('lead_status_relation')) {
                $query->where('lead_status', $request->input('lead_status_relation'));
            }

            if ($request->has('priority_id') && $request->input('priority_id')) {
                $query->where('priority_id', $request->input('priority_id'));
            }

            if ($request->has('status') && $request->input('status')) {
                $query->where('status', $request->input('status'));
            }

            // Apply search if provided
            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            }

            $leads = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format the response with only the requested fields
            $formattedLeads = $leads->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'brand_name' => $lead->brand?->name ?? null,
                    'agency_name' => $lead->agency?->name ?? null,
                    'assign_to' => $lead->assignedUser?->name ?? null,
                    'call_status' => $lead->callStatusRelation?->name ?? null,
                    'lead_status' => $lead->leadStatusRelation?->name ?? null,
                    'priority_name' => $lead->priority?->name ?? null,
                    'contact_person_name' => $lead->name,
                    'created_at' => $lead->created_at?->format('Y-m-d H:i:s A'),
                ];
            });

            return $this->responseService->paginated(
                $formattedLeads,
                'Lead activities retrieved successfully',
                $leads
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
     * Display the specified lead.
     *
     * GET /leads/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->getLead($id);

            if (!$lead) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            // Authorize the user to view this lead
            // $this->authorize('view', $lead);

            return $this->responseService->success(
                new LeadResource($lead),
                'Lead retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created lead in storage.
     *
     * POST /leads
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'profile_url' => 'nullable|string|max:255',
                'mobile_number' => 'sometimes|required|array',
                'mobile_number.*' => 'regex:/^[0-9]{10}$/|distinct|unique:lead_mobile_numbers,mobile_number',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
                'current_assign_user' => 'nullable|integer|exists:users,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'call_status_id' => 'nullable|integer|exists:call_statuses,id',
                'type' => 'required|in:Agency,Brand',
                'designation_id' => 'required|integer|exists:designations,id',
                'department_id' => 'required|integer|exists:departments,id',
                'sub_source_id' => 'required|integer|exists:lead_sub_source,id',
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'nullable|integer|exists:states,id',
                'city_id' => 'nullable|integer|exists:cities,id',
                'zone_id' => 'nullable|integer|exists:zones,id',
                'postal_code' => 'nullable|string|max:20',
                'comment' => 'nullable|string|max:1000',
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            // Validate that mobile numbers in the array are unique (no duplicates within the array)
            if (!empty($validatedData['mobile_number'])) {
                $mobileNumbers = $validatedData['mobile_number'];
                $uniqueMobileNumbers = array_unique($mobileNumbers);
                if (count($mobileNumbers) !== count($uniqueMobileNumbers)) {
                    return $this->responseService->validationError(
                        ['mobile_number' => ['Mobile numbers must be unique. Duplicate numbers are not allowed.']],
                        'Validation failed'
                    );
                }

                // Check if any mobile number already exists in the database (excluding soft-deleted)
                $existingMobileNumbers = \App\Models\LeadMobileNumber::whereIn('mobile_number', $mobileNumbers)
                    ->exists();
                if ($existingMobileNumbers) {
                    return $this->responseService->validationError(
                        ['mobile_number' => ['One or more mobile numbers already exist in the database. Mobile numbers must be unique across all leads.']],
                        'Validation failed'
                    );
                }
            }

            // Validate that exactly ONE of brand_id or agency_id is provided
            $hasBrandId = !empty($validatedData['brand_id']);
            $hasAgencyId = !empty($validatedData['agency_id']);

            if (($hasBrandId && $hasAgencyId) || (!$hasBrandId && !$hasAgencyId)) {
                return $this->responseService->validationError(
                    ['brand_id' => ['Select either brand_id or agency_id, not both and not neither.']],
                    'Validation failed'
                );
            }

            // Set default status to activated if not provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = '1';
            }

            $lead = $this->leadService->createLead($validatedData);

            return $this->responseService->created(
                new LeadResource($lead),
                'Lead created successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update the specified lead in storage.
     *
     * PUT /leads/{id}
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
                'email' => 'sometimes|nullable|email|max:255',
                'profile_url' => 'sometimes|nullable|string|max:255',

                'mobile_number' => 'sometimes|required|array',

                'mobile_number.*' => [
                    'required',
                    'regex:/^[0-9]{10}$/',
                    'distinct',
                    Rule::unique('lead_mobile_numbers', 'mobile_number')
                        ->where(fn ($q) => $q->where('lead_id', '!=', $id)),
                ],

                'brand_id' => 'sometimes|nullable|integer|exists:brands,id',
                'agency_id' => 'sometimes|nullable|integer|exists:agency,id',
                'current_assign_user' => 'sometimes|nullable|integer|exists:users,id',
                'priority_id' => 'sometimes|nullable|integer|exists:priorities,id',
                'call_status_id' => 'sometimes|nullable|integer|exists:call_statuses,id',
                'type' => 'sometimes|nullable|in:Agency,Brand',
                'designation_id' => 'sometimes|nullable|integer|exists:designations,id',
                'department_id' => 'sometimes|nullable|integer|exists:departments,id',
                'sub_source_id' => 'sometimes|nullable|integer|exists:lead_sub_source,id',
                'country_id' => 'sometimes|nullable|integer|exists:countries,id',
                'state_id' => 'sometimes|nullable|integer|exists:states,id',
                'city_id' => 'sometimes|nullable|integer|exists:cities,id',
                'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'comment' => 'sometimes|nullable|string|max:1000',
                'status' => 'sometimes|nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            // If updating brand_id or agency_id, validate that exactly ONE is selected
            if ($request->has('brand_id') || $request->has('agency_id')) {
                $hasBrandId = !empty($validatedData['brand_id']);
                $hasAgencyId = !empty($validatedData['agency_id']);

                if (($hasBrandId && $hasAgencyId) || (!$hasBrandId && !$hasAgencyId)) {
                    return $this->responseService->validationError(
                        ['brand_id' => ['Select either brand_id or agency_id, not both and not neither.']],
                        'Validation failed'
                    );
                }
            }

            // Only pass data that was actually provided in the request
            $dataToUpdate = array_filter($validatedData, function ($key) use ($request) {
                return $request->has($key);
            }, ARRAY_FILTER_USE_KEY);

            $this->leadService->updateLead($id, $dataToUpdate);

            // Fetch updated lead
            $lead = $this->leadService->getLead($id);

            if (!$lead) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            return $this->responseService->updated(
                new LeadResource($lead),
                'Lead updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove the specified lead from storage (Soft Delete).
     *
     * DELETE /leads/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->getLead($id);

            if (!$lead) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            $this->leadService->deleteLead($id);

            return $this->responseService->deleted('Lead deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of leads (for dropdowns)
     *
     * GET /leads/list
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        try {
            $leadsList = $this->leadService->getLeadList();

            return $this->responseService->success(
                $leadsList,
                'Lead list retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Display the latest two leads.
     *
     * GET /leads/latest/two
     *
     * @return JsonResponse
     */
    public function latestTwo(): JsonResponse
    {
        try {
            $leads = Lead::with([
                'brand',
                'agency',
                'assignedUser',
                'callStatusRelation',
                'priority'
            ])->accessibleToUser()
              ->orderBy('created_at', 'desc')
              ->limit(2)
              ->get();

            return $this->responseService->success(
                LeadResource::collection($leads),
                'Latest two leads retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get the latest two follow-up leads.
     *
     * GET /leads/latest/follow-up-two
     *
     * @return JsonResponse
     */
    public function latestTwoFollowUp(): JsonResponse
    {
        try {
            $leads = $this->leadService->getLatestTwoFollowUpLeads();

            return $this->responseService->success(
                LeadResource::collection($leads),
                'Latest two follow-up leads retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get the latest two meeting-scheduled leads.
     *
     * GET /leads/latest/meeting-scheduled-two
     *
     * @return JsonResponse
     */
    public function latestTwoMeetingScheduled(): JsonResponse
    {
        try {
            $leads = $this->leadService->getLatestTwoMeetingScheduledLeads();

            return $this->responseService->success(
                LeadResource::collection($leads),
                'Latest two meeting scheduled leads retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get the latest two meeting-done leads.
     *
     * GET /leads/latest/meeting-done-two
     *
     * @return JsonResponse
     */
    public function latestTwoMeetingDone(): JsonResponse
    {
        try {
            $leads = $this->leadService->getLatestTwoMeetingDoneLeads();

            return $this->responseService->success(
                LeadResource::collection($leads),
                'Latest two meeting done leads retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Assign lead to a user.
     *
     * POST /leads/{id}/assign
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $this->leadService->assignLeadToUser($id, $request->input('user_id'));

            $lead = $this->leadService->getLead($id);

            return $this->responseService->updated(
                new LeadResource($lead),
                'Lead assigned successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update assigned user for a lead.
     *
     * PUT /leads/{id}/assign-user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateAssignedUser(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $this->leadService->assignLeadToUser($id, $request->input('user_id'));

            $lead = $this->leadService->getLead($id);

            return $this->responseService->updated(
                new LeadResource($lead),
                'Lead assigned user updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update lead priority.
     *
     * POST /leads/{id}/priority
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePriority(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'priority_id' => 'required|integer|exists:priorities,id',
            ]);

            $this->leadService->updateLeadPriority($id, $request->input('priority_id'));

            $lead = $this->leadService->getLead($id);

            return $this->responseService->updated(
                new LeadResource($lead),
                'Lead priority updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update lead status.
     *
     * POST /leads/{id}/status
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'status' => 'required|in:1,2,15',
            ]);

            $this->leadService->updateLeadStatus($id, $request->input('status'));

            $lead = $this->leadService->getLead($id);

            return $this->responseService->updated(
                new LeadResource($lead),
                'Lead status updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Add call status to lead.
     *
     * POST /leads/{id}/call-status
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addCallStatus(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'call_status_id' => 'required|integer|exists:call_statuses,id',
            ]);

            $this->leadService->addCallStatus($id, $request->input('call_status_id'));

            $lead = $this->leadService->getLead($id);

            return $this->responseService->updated(
                new LeadResource($lead),
                'Call status added successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (DomainException $e) {
            return $this->responseService->notFound($e->getMessage());
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove call status from lead.
     *
     * DELETE /leads/{id}/call-status/{callStatusId}
     *
     * @param int $id
     * @param int $callStatusId
     * @return JsonResponse
     */
    public function removeCallStatus(int $id, int $callStatusId): JsonResponse
    {
        try {
            $this->leadService->removeCallStatus($id, $callStatusId);

            $lead = $this->leadService->getLead($id);
            return $this->responseService->updated(
                new LeadResource($lead),
                'Call status removed successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get leads by filter criteria.
     *
     * GET /leads/filter
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'agency_id' => 'nullable|integer|exists:agency,id',
                'current_assign_user' => 'nullable|integer|exists:users,id',
                'priority_id' => 'nullable|integer|exists:priorities,id',
                'status' => 'nullable|in:1,2,15', 
                'type' => 'nullable|string|max:50',
                'country_id' => 'nullable|integer|exists:countries,id',
                'state_id' => 'nullable|integer|exists:states,id',
                'city_id' => 'nullable|integer|exists:cities,id',
                'search' => 'nullable|string|max:255',
            ]);

            $perPage = (int) $request->input('per_page', 15);
            $filters = $request->except(['per_page']);

            $leads = $this->leadService->getLeadsWithFilters($filters, $perPage);

            return $this->responseService->paginated(
                LeadResource::collection($leads),
                'Leads retrieved successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get contact persons (leads) by brand ID with complete data.
     *
     * GET /leads/contact-persons/by-brand/{brandId}
     *
     * @param int $brandId
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactPersonsByBrand(int $brandId, Request $request): JsonResponse
    {
        try {
            // Validate request parameters
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            // Validate that the brand exists
            $brand = \App\Models\Brand::find($brandId);
            if (!$brand) {
                return $this->responseService->notFound('Brand not found');
            }

            $perPage = (int) $request->input('per_page', 10);

            // Get paginated leads
            $leads = \App\Models\Lead::with([
                'brand',
                'agency',
                'assignedUser',
                'createdByUser',
                'priority',
                'designation',
                'department',
                'subSource',
                'country',
                'state',
                'city',
                'zone',
                'statusRelation',
                'callStatusRelation',
                'leadStatusRelation',
            ])
            ->where('brand_id', $brandId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
            return $this->responseService->paginated(
                LeadResource::collection($leads),
                'Contact persons retrieved successfully'
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
     * Get contact persons (leads) by agency ID with complete data.
     *
     * GET /leads/contact-persons/by-agency/{agencyId}
     *
     * @param int $agencyId
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactPersonsByAgency(int $agencyId, Request $request): JsonResponse
    {
        try {
            // Validate request parameters
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            // Validate that the agency exists
            $agency = \App\Models\Agency::find($agencyId);
            if (!$agency) {
                return $this->responseService->notFound('Agency not found');
            }

            $perPage = (int) $request->input('per_page', 10);

            // Get paginated leads
            $leads = \App\Models\Lead::with([
                'brand',
                'agency',
                'assignedUser',
                'createdByUser',
                'priority',
                'designation',
                'department',
                'subSource',
                'country',
                'state',
                'city',
                'zone',
                'statusRelation',
                'callStatusRelation',
                'leadStatusRelation',
            ])
            ->where('agency_id', $agencyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
            return $this->responseService->paginated(
                LeadResource::collection($leads),
                'Contact persons retrieved successfully'
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
     * Get lead assignment history by lead ID.
     *
     * GET /leads/{id}/history
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getHistory(int $id, Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $perPage = (int) $request->input('per_page', 10);
            $history = $this->leadService->getLeadHistory($id, $perPage);

            return $this->responseService->paginated(
                $history,
                'Lead history retrieved successfully'
            );
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), ['status' => 404]);
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
     * Get all pending leads.
     *
     * GET /leads/pending
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pendingLeads(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1',
            ]);

            $perPage = (int) $request->input('per_page', 3);

            // This must return LengthAwarePaginator
            $pendingLeads = $this->leadService->getPendingLeads($perPage);

            // Transform paginator items WITHOUT destroying paginator
            if ($pendingLeads instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $pendingLeads->setCollection(
                    $pendingLeads->getCollection()->map(function ($lead) {
                        return [
                            'id' => $lead->id,
                            'name' => $lead->name,
                            'priority' => [
                                'id' => $lead->priority?->id,
                                'name' => $lead->priority?->name,
                            ],
                            'created_at' => $lead->created_at?->format('Y-m-d H:i:s A'),
                        ];
                    })
                );
            };

            // Send paginator directly
            return $this->responseService->paginated(
                $pendingLeads,
                'Pending leads retrieved successfully'
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