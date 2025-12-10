<?php

namespace App\Repositories;

use App\Contracts\Repositories\LeadRepositoryInterface;
use App\Models\Lead;
use App\Models\LeadAssignHistory;
use App\Models\Priority;
use App\Models\Status;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use DomainException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LeadRepository implements LeadRepositoryInterface
{
    /**
     * Default relationships to eager load.
     *
     * @var array<string>
     */
    protected const DEFAULT_RELATIONSHIPS = [
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
    ];

    /**
     * @var Lead
     */
    protected Lead $model;

    /**
     * Create a new LeadRepository instance.
     *
     * @param Lead $lead
     */
    public function __construct(Lead $lead)
    {
        $this->model = $lead;
    }

    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Fetch paginated list of leads with relationships.
     *
     * @param int $perPage
     * @param string|null $searchTerm
     * @return LengthAwarePaginator
     */
    public function getAllLeads(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->model
            ->with(self::DEFAULT_RELATIONSHIPS);

        // Apply search filter if search term is provided
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                      $brandQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('assignedUser', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('leadStatusRelation', function ($statusQuery) use ($searchTerm) {
                      $statusQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch a single lead by its primary ID.
     *
     * @param int $id
     * @return Lead|null
     */
    public function getLeadById(int $id): ?Lead
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->find($id);
    }

    /**
     * Fetch leads by brand ID.
     *
     * @param int $brandId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeadsByBrandId(int $brandId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('brand_id', $brandId)
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch leads by agency ID.
     *
     * @param int $agencyId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeadsByAgencyId(int $agencyId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('agency_id', $agencyId)
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch leads assigned to a specific user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeadsByAssignedUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('current_assign_user', $userId)
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch leads by status.
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeadsByStatus(string $status, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Fetch leads by priority ID.
     *
     * @param int $priorityId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeadsByPriority(int $priorityId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('priority_id', $priorityId)
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Get a simple list of leads (ID and Name).
     *
     * @return Collection|null
     */
    public function getLeadList(): ?Collection
    {
        return $this->model
            ->select('id', 'name')
            ->where('status', '1')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Fetch leads by multiple criteria.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeadsWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(self::DEFAULT_RELATIONSHIPS);

        // Apply filters if provided
        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['agency_id'])) {
            $query->where('agency_id', $filters['agency_id']);
        }

        if (isset($filters['current_assign_user'])) {
            $query->where('current_assign_user', $filters['current_assign_user']);
        }

        if (isset($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', '1');
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (isset($filters['state_id'])) {
            $query->where('state_id', $filters['state_id']);
        }

        if (isset($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('profile_url', 'LIKE', "%{$search}%");
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new lead record.
     * If call_status_id is provided, automatically sets call_status and lead_status.
     *
     * @param array<string, mixed> $data
     * @return Lead
     */
    public function createLead(array $data): Lead
    {
        try {
            // Generate UUID if not provided
            if (!isset($data['uuid'])) {
                $data['uuid'] = (string) Str::uuid();
            }
            
            // Generate slug from name if not provided
            if (!isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name'] ?? 'lead-' . time());
            }
            
            // Set created_by to the currently authenticated user if not provided
            if (!isset($data['created_by'])) {
                $currentUser = Auth::user();
                if ($currentUser) {
                    $data['created_by'] = $currentUser->id;
                }
            }
            
            // Initialize array fields if not provided
            if (!isset($data['mobile_number'])) {
                $data['mobile_number'] = [];
            }
            
            // Ensure mobile_number is an array
            if (is_string($data['mobile_number'])) {
                $data['mobile_number'] = [$data['mobile_number']];
            }
            
            // Handle call_status_id: convert to call_status and lead_status
            if (isset($data['call_status_id']) && !empty($data['call_status_id'])) {
                $callStatusId = $data['call_status_id'];
                $data['call_status'] = $callStatusId;
                
                // Find the Status that contains this callStatusId
                $statusRecord = null;
                $allStatuses = Status::all();
                
                foreach ($allStatuses as $status) {
                    // call_status is stored as JSON array
                    $callStatuses = is_string($status->call_status) 
                        ? json_decode($status->call_status, true) 
                        : $status->call_status;
                    
                    if (is_array($callStatuses) && in_array($callStatusId, $callStatuses)) {
                        $statusRecord = $status;
                        break;
                    }
                }
                
                // If matching status found, set lead_status
                if ($statusRecord) {
                    $data['lead_status'] = $statusRecord->id;
                }
                
                // Find and set priority based on call_status_id
                $priorityRecord = null;
                $allPriorities = Priority::all();
                
                foreach ($allPriorities as $priority) {
                    // call_status is stored as JSON array in Priority model
                    $priorityCallStatuses = is_string($priority->call_status) 
                        ? json_decode($priority->call_status, true) 
                        : $priority->call_status;
                    
                    if (is_array($priorityCallStatuses) && in_array($callStatusId, $priorityCallStatuses)) {
                        $priorityRecord = $priority;
                        break;
                    }
                }
                
                // If matching priority found, set priority_id
                if ($priorityRecord) {
                    $data['priority_id'] = $priorityRecord->id;
                }
                
                // Remove call_status_id from data as it's not a column
                unset($data['call_status_id']);
            }
            
            // Remove null values to avoid validation errors
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });
            
            return $this->model->create($data);
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error creating lead', ['data' => $data, 'exception' => $e->getMessage()]);
            throw new DomainException('Database error while creating lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error creating lead', ['data' => $data, 'exception' => $e->getMessage()]);
            throw new DomainException('Unexpected error while creating lead.');
        }
    }

    /**
     * Update an existing lead by ID.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateLead(int $id, array $data): bool
    {
        try {
            $lead = $this->model->findOrFail($id);
            
            // If name is being updated, also update the slug
            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }
            
            // Handle brand_id and agency_id updates
            // If brand_id is being set, clear agency_id
            if (isset($data['brand_id']) && !empty($data['brand_id'])) {
                $data['agency_id'] = null;
            }
            
            // If agency_id is being set, clear brand_id
            if (isset($data['agency_id']) && !empty($data['agency_id'])) {
                $data['brand_id'] = null;
            }
            
            // Handle call_status_id: convert to call_status, lead_status, and priority_id
            if (isset($data['call_status_id']) && !empty($data['call_status_id'])) {
                $callStatusId = $data['call_status_id'];
                $data['call_status'] = $callStatusId;
                
                // Find the Status that contains this callStatusId
                $statusRecord = null;
                $allStatuses = Status::all();
                
                foreach ($allStatuses as $status) {
                    // call_status is stored as JSON array
                    $callStatuses = is_string($status->call_status) 
                        ? json_decode($status->call_status, true) 
                        : $status->call_status;
                    
                    if (is_array($callStatuses) && in_array($callStatusId, $callStatuses)) {
                        $statusRecord = $status;
                        break;
                    }
                }
                
                // If matching status found, set lead_status
                if ($statusRecord) {
                    $data['lead_status'] = $statusRecord->id;
                }
                
                // Find and set priority based on call_status_id
                $priorityRecord = null;
                $allPriorities = Priority::all();
                
                foreach ($allPriorities as $priority) {
                    // call_status is stored as JSON array in Priority model
                    $priorityCallStatuses = is_string($priority->call_status) 
                        ? json_decode($priority->call_status, true) 
                        : $priority->call_status;
                    
                    if (is_array($priorityCallStatuses) && in_array($callStatusId, $priorityCallStatuses)) {
                        $priorityRecord = $priority;
                        break;
                    }
                }
                
                // If matching priority found, set priority_id
                if ($priorityRecord) {
                    $data['priority_id'] = $priorityRecord->id;
                }
                
                // Remove call_status_id from data as it's not a column
                unset($data['call_status_id']);
            }
            
            // Save history before update (captures old data)
            $this->saveLeadHistory($lead);
            
            $result = $lead->update($data);
            
            return $result;
        } catch (QueryException $e) {
            Log::error('Database error updating lead', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while updating lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating lead', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating lead.');
        }
    }

    /**
     * Assign a lead to a user.
     *
     * @param int $leadId
     * @param int $userId
     * @return bool
     */
    public function assignLeadToUser(int $leadId, int $userId): bool
    {
        try {
            $lead = $this->model->findOrFail($leadId);
            
            // Save history before update (captures old data)
            $this->saveLeadHistory($lead);
            
            $result = $lead->update(['current_assign_user' => $userId]);
            
            return $result;
        } catch (QueryException $e) {
            Log::error('Database error assigning lead', ['lead_id' => $leadId, 'user_id' => $userId, 'exception' => $e]);
            throw new DomainException('Database error while assigning lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error assigning lead', ['lead_id' => $leadId, 'user_id' => $userId, 'exception' => $e]);
            throw new DomainException('Unexpected error while assigning lead.');
        }
    }

    /**
     * Update the priority of a lead.
     *
     * @param int $leadId
     * @param int $priorityId
     * @return bool
     */
    public function updateLeadPriority(int $leadId, int $priorityId): bool
    {
        try {
            $lead = $this->model->findOrFail($leadId);
            
            // Save history before update (captures old data)
            $this->saveLeadHistory($lead);
            
            $result = $lead->update(['priority_id' => $priorityId]);
            
            return $result;
        } catch (QueryException $e) {
            Log::error('Database error updating lead priority', ['lead_id' => $leadId, 'priority_id' => $priorityId, 'exception' => $e]);
            throw new DomainException('Database error while updating lead priority.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating lead priority', ['lead_id' => $leadId, 'priority_id' => $priorityId, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating lead priority.');
        }
    }

    /**
     * Update the status of a lead.
     *
     * @param int $leadId
     * @param string $status
     * @return bool
     */
    public function updateLeadStatus(int $leadId, string $status): bool
    {
        try {
            $lead = $this->model->findOrFail($leadId);
            
            // Save history before update (captures old data)
            $this->saveLeadHistory($lead);
            
            $result = $lead->update(['status' => $status]);
            
            return $result;
        } catch (QueryException $e) {
            Log::error('Database error updating lead status', ['lead_id' => $leadId, 'status' => $status, 'exception' => $e]);
            throw new DomainException('Database error while updating lead status.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating lead status', ['lead_id' => $leadId, 'status' => $status, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating lead status.');
        }
    }

    /**
     * Add call status to a lead.
     *
     * @param int $leadId
     * @param int $callStatusId
     * @return bool
     */
    public function addCallStatus(int $leadId, int $callStatusId): bool
    {
        try {
            $lead = $this->model->findOrFail($leadId);
            
            // Get all statuses to find which one contains this callStatusId
            $statusRecord = null;
            $allStatuses = Status::all();
            
            foreach ($allStatuses as $status) {
                // call_status is stored as JSON array
                $callStatuses = is_string($status->call_status) 
                    ? json_decode($status->call_status, true) 
                    : $status->call_status;
                
                if (is_array($callStatuses) && in_array($callStatusId, $callStatuses)) {
                    $statusRecord = $status;
                    break;
                }
            }
            
            // Get priority based on call_status mapping
            $priorityId = null;
            $allPriorities = Priority::all();
            foreach ($allPriorities as $priority) {
                $priorityCallStatuses = is_string($priority->call_status) 
                    ? json_decode($priority->call_status, true) 
                    : $priority->call_status;
                
                if (is_array($priorityCallStatuses) && in_array($callStatusId, $priorityCallStatuses)) {
                    $priorityId = $priority->id;
                    break;
                }
            }
            
            Log::info('Adding call status', [
                'lead_id' => $leadId,
                'call_status_id' => $callStatusId,
                'status_record_found' => $statusRecord ? 'Yes' : 'No',
                'status_id' => $statusRecord?->id,
                'priority_id' => $priorityId,
            ]);
            
            // Prepare update data
            $updateData = [
                'call_status' => $callStatusId,
                'call_attempt' => $lead->call_attempt + 1, // Increment call attempt count
            ];
            
            // If matching status found, also update lead_status (the lead's status)
            if ($statusRecord) {
                $updateData['lead_status'] = $statusRecord->id;
            }
            
            // If priority found, update it
            if ($priorityId) {
                $updateData['priority_id'] = $priorityId;
            }
            
            // Save history before update (captures old data)
            $this->saveLeadHistory($lead);
            
            $result = $lead->update($updateData);
            
            return $result;
        } catch (QueryException $e) {
            Log::error('Database error adding call status', ['lead_id' => $leadId, 'call_status_id' => $callStatusId, 'exception' => $e]);
            throw new DomainException('Database error while adding call status.');
        } catch (Exception $e) {
            Log::error('Unexpected error adding call status', ['lead_id' => $leadId, 'call_status_id' => $callStatusId, 'exception' => $e]);
            throw new DomainException('Unexpected error while adding call status.');
        }
    }

    /**
     * Remove call status from a lead.
     *
     * @param int $leadId
     * @param int $callStatusId
     * @return bool
     */
    public function removeCallStatus(int $leadId, int $callStatusId): bool
    {
        try {
            $lead = $this->model->findOrFail($leadId);
            
            // Only remove if the current call_status matches the provided one
            if ($lead->call_status === $callStatusId) {
                // Save history before update (captures old data)
                $this->saveLeadHistory($lead);
                
                $result = $lead->update([
                    'call_status' => null,
                    'lead_status' => null,
                ]);
                
                return $result;
            }
            
            return true;
        } catch (QueryException $e) {
            Log::error('Database error removing call status', ['lead_id' => $leadId, 'call_status_id' => $callStatusId, 'exception' => $e]);
            throw new DomainException('Database error while removing call status.');
        } catch (Exception $e) {
            Log::error('Unexpected error removing call status', ['lead_id' => $leadId, 'call_status_id' => $callStatusId, 'exception' => $e]);
            throw new DomainException('Unexpected error while removing call status.');
        }
    }

    /**
     * Soft delete a lead by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteLead(int $id): bool
    {
        try {
            $lead = $this->model->findOrFail($id);
            return $lead->delete();
        } catch (QueryException $e) {
            Log::error('Database error deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while deleting lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while deleting lead.');
        }
    }

    /**
     * Permanently delete a lead by ID.
     *
     * @param int $id
     * @return bool
     */
    public function forceDeleteLead(int $id): bool
    {
        try {
            $lead = $this->model->findOrFail($id);
            return $lead->forceDelete();
        } catch (QueryException $e) {
            Log::error('Database error force deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while force deleting lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error force deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while force deleting lead.');
        }
    }

    /**
     * Save lead update history to lead_assign_histories table.
     *
     * @param Lead $lead
     * @return void
     */
    private function saveLeadHistory(Lead $lead): void
    {
        try {
            // Get current authenticated user
            $currentUserId = Auth::check() ? Auth::id() : null;

            // Create history record
            LeadAssignHistory::create([
                'uuid' => Str::uuid(),
                'lead_id' => $lead->id,
                'assign_user_id' => $lead->current_assign_user,
                'current_user_id' => $currentUserId,
                'priority_id' => $lead->priority_id,
                'lead_status_id' => $lead->lead_status,
                'call_status_id' => $lead->call_status,
                'status' => $lead->status,
            ]);
        } catch (Exception $e) {
            // Log error but don't throw to prevent breaking the update operation
            Log::warning('Failed to record lead history', [
                'lead_id' => $lead->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
