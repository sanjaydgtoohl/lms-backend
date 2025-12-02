<?php

namespace App\Repositories;

use App\Contracts\Repositories\MeetingRepositoryInterface;
use App\Models\Meeting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EloquentMeetingRepository implements MeetingRepositoryInterface
{
    protected $model;

    public function __construct(Meeting $model)
    {
        $this->model = $model;
    }

    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Fetch paginated list of meetings with optional search.
     */
    public function getAllMeetings(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->model->with(['lead', 'attendee']);

        // Apply search filter if provided
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('agenda', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Fetch all meetings without pagination.
     */
    public function getAll(): Collection
    {
        return $this->model->with(['lead', 'attendee'])->latest()->get();
    }

    /**
     * Fetch a single meeting by its primary ID.
     */
    public function getById(int $id): ?Meeting
    {
        return $this->model->with(['lead', 'attendee'])->find($id);
    }

    /**
     * Fetch a single meeting by its UUID.
     */
    public function getByUuid(string $uuid): ?Meeting
    {
        return $this->model->with(['lead', 'attendee'])->where('uuid', $uuid)->first();
    }

    /**
     * Fetch all meetings for a specific lead.
     */
    public function getByLead(int $leadId): Collection
    {
        return $this->model->with(['lead', 'attendee'])
                           ->where('lead_id', $leadId)
                           ->latest()
                           ->get();
    }

    /**
     * Fetch all meetings for a specific attendee.
     */
    public function getByAttendee(int $attendeeId): Collection
    {
        return $this->model->with(['lead', 'attendee'])
                           ->where('attendees_id', $attendeeId)
                           ->latest()
                           ->get();
    }

    /**
     * Fetch meetings by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->with(['lead', 'attendee'])
                           ->where('status', $status)
                           ->latest()
                           ->get();
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new meeting.
     */
    public function create(array $data): Meeting
    {
        // Generate UUID if not provided
        if (!isset($data['uuid'])) {
            $data['uuid'] = Str::uuid();
        }

        // Generate slug from title if not provided
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $this->model->create($data);
    }

    /**
     * Update an existing meeting.
     */
    public function update(int $id, array $data): Meeting
    {
        $meeting = $this->model->findOrFail($id);

        // Update slug if title is being updated
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $meeting->update($data);

        return $meeting->fresh(['lead', 'attendee']);
    }

    /**
     * Delete a meeting (hard delete).
     */
    public function delete(int $id): bool
    {
        $meeting = $this->model->findOrFail($id);
        return $meeting->forceDelete();
    }

    /**
     * Soft delete a meeting.
     */
    public function softDelete(int $id): bool
    {
        $meeting = $this->model->findOrFail($id);
        return $meeting->delete();
    }

    /**
     * Restore a soft-deleted meeting.
     */
    public function restore(int $id): bool
    {
        $meeting = $this->model->withTrashed()->findOrFail($id);
        return $meeting->restore();
    }
}
