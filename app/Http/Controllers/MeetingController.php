<?php

namespace App\Http\Controllers;

use App\Services\MeetingService;
use App\Services\ResponseService;
use App\Http\Resources\MeetingResource;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Controller for managing meetings.
 * 
 * Handles CRUD operations for meetings and their relationships with leads and attendees.
 */
class MeetingController extends Controller
{
    use ValidatesRequests;

    protected $meetingService;
    protected $responseService;

    /**
     * Create a new MeetingController instance.
     *
     * @param MeetingService $meetingService Service for meeting operations
     * @param ResponseService $responseService Service for standardized API responses
     */
    public function __construct(MeetingService $meetingService, ResponseService $responseService)
    {
        $this->meetingService = $meetingService;
        $this->responseService = $responseService;
    }

    /**
     * Get paginated list of meetings
     * GET /api/v1/meetings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $search = $request->input('search', null);
            
            $meetings = $this->meetingService->getAllMeetings($perPage, $search);
            return $this->responseService->paginated(
                MeetingResource::collection($meetings),
                'Meetings retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of meetings with only id and title
     * GET /api/v1/meetings/list
     */
    public function list(): JsonResponse
    {
        try {
            $meetings = $this->meetingService->getAll();
            $data = $meetings->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'uuid' => $meeting->uuid,
                    'title' => $meeting->title,
                ];
            });
            return $this->responseService->success($data, 'Meetings list retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get all meetings without pagination
     * GET /api/v1/meetings/all
     */
    public function getAll(): JsonResponse
    {
        try {
            $meetings = $this->meetingService->getAll();
            return $this->responseService->success(MeetingResource::collection($meetings), 'All meetings retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get all meetings for a specific lead
     * GET /leads/{leadId}/meetings
     */
    public function getMeetingsByLead(int $leadId): JsonResponse
    {
        try {
            $meetings = $this->meetingService->getMeetingsByLead($leadId);
            $data = MeetingResource::collection($meetings);
            return $this->responseService->success($data, 'Meetings for lead retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get all meetings for a specific attendee
     * GET /users/{attendeeId}/meetings
     */
    public function getMeetingsByAttendee(int $attendeeId): JsonResponse
    {
        try {
            $meetings = $this->meetingService->getMeetingsByAttendee($attendeeId);
            $data = MeetingResource::collection($meetings);
            return $this->responseService->success($data, 'Meetings for attendee retrieved');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Store a newly created meeting
     * POST /api/v1/meetings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'title' => 'required|string|max:255',
                'lead_id' => 'required|integer|exists:leads,id',
                'attendees_id' => 'nullable|integer|exists:users,id',
                'type' => 'required|in:face_to_face,online',
                'location' => 'nullable|string|max:255',
                'agenda' => 'nullable|string',
                'link' => 'nullable|string|url',
                'meeting_date' => 'nullable|date',
                'meeting_time' => 'nullable|date_format:H:i',
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            $meeting = $this->meetingService->createMeeting($validatedData);
            $data = new MeetingResource($meeting);
            return $this->responseService->created($data, 'Meeting created successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Display the specified meeting
     * GET /api/v1/meetings/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $meeting = $this->meetingService->getMeetingById($id);
            $data = new MeetingResource($meeting);
            return $this->responseService->success($data, 'Meeting retrieved successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Update the specified meeting
     * PUT /api/v1/meetings/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $rules = [
                'title' => 'required|string|max:255',
                'lead_id' => 'required|integer|exists:leads,id',
                'attendees_id' => 'nullable|integer|exists:users,id',
                'type' => 'required|in:face_to_face,online',
                'location' => 'nullable|string|max:255',
                'agenda' => 'nullable|string',
                'link' => 'nullable|string|url',
                'meeting_date' => 'nullable|date',
                'meeting_time' => 'nullable|date_format:H:i',
                'status' => 'nullable|in:1,2,15',
            ];

            $validatedData = $this->validate($request, $rules);

            $meeting = $this->meetingService->updateMeeting($id, $validatedData);
            $data = new MeetingResource($meeting);
            return $this->responseService->updated($data, 'Meeting updated successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Remove the specified meeting from storage (soft delete)
     * DELETE /api/v1/meetings/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->meetingService->softDeleteMeeting($id);
            return $this->responseService->deleted('Meeting deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Restore a soft-deleted meeting
     * PATCH /api/v1/meetings/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $this->meetingService->restoreMeeting($id);
            return $this->responseService->success(null, 'Meeting restored successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
