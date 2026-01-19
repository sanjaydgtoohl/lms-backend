<?php

namespace App\Services;

use App\Contracts\Repositories\LeadRepositoryInterface;
use App\Models\Lead;
use App\Models\LeadMobileNumber;
use DomainException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LeadService
{
    /**
     * @var LeadRepositoryInterface
     */
    protected LeadRepositoryInterface $leadRepository;

    /**
     * Create a new LeadService instance.
     *
     * @param LeadRepositoryInterface $leadRepository
     */
    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * Get all leads with pagination.
     *
     * @param int $perPage
     * @param string|null $searchTerm
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getAllLeads(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getAllLeads($perPage, $searchTerm);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads', ['exception' => $e]);
            throw new DomainException('Database error while fetching leads.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads.');
        }
    }

    /**
     * Get a lead by ID.
     *
     * @param int $id
     * @return Lead|null
     * @throws DomainException
     */
    public function getLead(int $id): ?Lead
    {
        try {
            return $this->leadRepository->getLeadById($id);
        } catch (QueryException $e) {
            Log::error('Database error fetching lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while fetching lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching lead.');
        }
    }

    /**
     * Get leads by brand ID.
     *
     * @param int $brandId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadsByBrand(int $brandId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getLeadsByBrandId($brandId, $perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads by brand', ['brand_id' => $brandId, 'exception' => $e]);
            throw new DomainException('Database error while fetching leads by brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads by brand', ['brand_id' => $brandId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads by brand.');
        }
    }

    /**
     * Get leads by agency ID.
     *
     * @param int $agencyId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadsByAgency(int $agencyId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getLeadsByAgencyId($agencyId, $perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads by agency', ['agency_id' => $agencyId, 'exception' => $e]);
            throw new DomainException('Database error while fetching leads by agency.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads by agency', ['agency_id' => $agencyId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads by agency.');
        }
    }

    /**
     * Get leads assigned to a specific user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadsByAssignedUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getLeadsByAssignedUser($userId, $perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads by assigned user', ['user_id' => $userId, 'exception' => $e]);
            throw new DomainException('Database error while fetching leads by assigned user.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads by assigned user', ['user_id' => $userId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads by assigned user.');
        }
    }

    /**
     * Get leads by status.
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadsByStatus(string $status, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getLeadsByStatus($status, $perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads by status', ['status' => $status, 'exception' => $e]);
            throw new DomainException('Database error while fetching leads by status.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads by status', ['status' => $status, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads by status.');
        }
    }

    /**
     * Get leads by priority.
     *
     * @param int $priorityId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadsByPriority(int $priorityId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getLeadsByPriority($priorityId, $perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads by priority', ['priority_id' => $priorityId, 'exception' => $e]);
            throw new DomainException('Database error while fetching leads by priority.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads by priority', ['priority_id' => $priorityId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads by priority.');
        }
    }

    /**
     * Get a simple list of leads (ID and Name).
     *
     * @return Collection|null
     * @throws DomainException
     */
    public function getLeadList(): ?Collection
    {
        try {
            return $this->leadRepository->getLeadList();
        } catch (QueryException $e) {
            Log::error('Database error fetching lead list', ['exception' => $e]);
            throw new DomainException('Database error while fetching lead list.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching lead list', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching lead list.');
        }
    }

    /**
     * Get leads with multiple filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadsWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getLeadsWithFilters($filters, $perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching leads with filters', ['filters' => $filters, 'exception' => $e]);
            throw new DomainException('Database error while fetching leads with filters.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching leads with filters', ['filters' => $filters, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching leads with filters.');
        }
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new lead.
     *
     * @param array $data
     * @return Lead
     * @throws DomainException
     */
    public function createLead(array $data): Lead
    {
        try {
            if (empty($data['name'])) {
                throw new DomainException('Lead name is required.');
            }

            return $this->leadRepository->createLead($data);
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error creating lead', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while creating lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error creating lead', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while creating lead.');
        }
    }

    /**
     * Update an existing lead.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws DomainException
     */
    public function updateLead(int $id, array $data): bool
    {
        try {
            return $this->leadRepository->updateLead($id, $data);
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
     * @throws DomainException
     */
    public function assignLeadToUser(int $leadId, int $userId): bool
    {
        try {
            return $this->leadRepository->assignLeadToUser($leadId, $userId);
        } catch (QueryException $e) {
            Log::error('Database error assigning lead to user', ['lead_id' => $leadId, 'user_id' => $userId, 'exception' => $e]);
            throw new DomainException('Database error while assigning lead to user.');
        } catch (Exception $e) {
            Log::error('Unexpected error assigning lead to user', ['lead_id' => $leadId, 'user_id' => $userId, 'exception' => $e]);
            throw new DomainException('Unexpected error while assigning lead to user.');
        }
    }

    /**
     * Update lead priority.
     *
     * @param int $leadId
     * @param int $priorityId
     * @return bool
     * @throws DomainException
     */
    public function updateLeadPriority(int $leadId, int $priorityId): bool
    {
        try {
            return $this->leadRepository->updateLeadPriority($leadId, $priorityId);
        } catch (QueryException $e) {
            Log::error('Database error updating lead priority', ['lead_id' => $leadId, 'priority_id' => $priorityId, 'exception' => $e]);
            throw new DomainException('Database error while updating lead priority.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating lead priority', ['lead_id' => $leadId, 'priority_id' => $priorityId, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating lead priority.');
        }
    }

    /**
     * Update lead status.
     *
     * @param int $leadId
     * @param string $status
     * @return bool
     * @throws DomainException
     */
    public function updateLeadStatus(int $leadId, string $status): bool
    {
        try {
            return $this->leadRepository->updateLeadStatus($leadId, $status);
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
     * @throws DomainException
     */
    public function addCallStatus(int $leadId, int $callStatusId): bool
    {
        try {
            return $this->leadRepository->addCallStatus($leadId, $callStatusId);
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
     * @throws DomainException
     */
    public function removeCallStatus(int $leadId, int $callStatusId): bool
    {
        try {
            return $this->leadRepository->removeCallStatus($leadId, $callStatusId);
        } catch (QueryException $e) {
            Log::error('Database error removing call status', ['lead_id' => $leadId, 'call_status_id' => $callStatusId, 'exception' => $e]);
            throw new DomainException('Database error while removing call status.');
        } catch (Exception $e) {
            Log::error('Unexpected error removing call status', ['lead_id' => $leadId, 'call_status_id' => $callStatusId, 'exception' => $e]);
            throw new DomainException('Unexpected error while removing call status.');
        }
    }

    /**
     * Delete a lead (soft delete).
     *
     * @param int $id
     * @return bool
     * @throws DomainException
     */
    public function deleteLead(int $id): bool
    {
        try {
            return $this->leadRepository->deleteLead($id);
        } catch (QueryException $e) {
            Log::error('Database error deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while deleting lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while deleting lead.');
        }
    }

    /**
     * Permanently delete a lead.
     *
     * @param int $id
     * @return bool
     * @throws DomainException
     */
    public function forceDeleteLead(int $id): bool
    {
        try {
            return $this->leadRepository->forceDeleteLead($id);
        } catch (QueryException $e) {
            Log::error('Database error force deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while force deleting lead.');
        } catch (Exception $e) {
            Log::error('Unexpected error force deleting lead', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while force deleting lead.');
        }
    }

    /**
     * Get all pending leads.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getPendingLeads(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->leadRepository->getPendingLeads($perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching pending leads', ['exception' => $e]);
            throw new DomainException('Database error while fetching pending leads.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching pending leads', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching pending leads.');
        }
    }

    /**
     * Get lead assignment history by lead ID.
     *
     * @param int $leadId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getLeadHistory(int $leadId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            // Verify the lead exists
            $lead = $this->leadRepository->getLeadById($leadId);
            if (!$lead) {
                throw new DomainException('Lead not found');
            }

            // Get the lead assign history
            return \App\Models\LeadAssignHistory::where('lead_id', $leadId)
                ->with(['assignedUser', 'currentUser', 'priority', 'status', 'callStatus'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching lead history', ['lead_id' => $leadId, 'exception' => $e]);
            throw new DomainException('Database error while fetching lead history.');
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Unexpected error fetching lead history', ['lead_id' => $leadId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching lead history.');
        }
    }

    /**
     * Add mobile numbers to a lead.
     *
     * @param int $leadId
     * @param array $mobileNumbers
     * @return bool
     * @throws DomainException
     */
    public function addMobileNumbers(int $leadId, array $mobileNumbers): bool
    {
        try {
            // Verify the lead exists
            $lead = $this->leadRepository->getLeadById($leadId);
            if (!$lead) {
                throw new DomainException('Lead not found');
            }

            // Delete existing mobile numbers for this lead
            LeadMobileNumber::where('lead_id', $leadId)->delete();

            // Add new mobile numbers
            $isFirst = true;
            foreach ($mobileNumbers as $number) {
                LeadMobileNumber::create([
                    'lead_id' => $leadId,
                    'mobile_number' => $number,
                    'is_primary' => $isFirst,
                    'is_verified' => false,
                ]);
                $isFirst = false;
            }

            return true;
        } catch (QueryException $e) {
            Log::error('Database error adding mobile numbers', ['lead_id' => $leadId, 'exception' => $e]);
            throw new DomainException('Database error while adding mobile numbers.');
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Unexpected error adding mobile numbers', ['lead_id' => $leadId, 'exception' => $e]);
            throw new DomainException('Unexpected error while adding mobile numbers.');
        }
    }

    /**
     * Get mobile numbers for a lead.
     *
     * @param int $leadId
     * @return Collection
     * @throws DomainException
     */
    public function getMobileNumbers(int $leadId): Collection
    {
        try {
            return LeadMobileNumber::where('lead_id', $leadId)
                ->orderBy('is_primary', 'desc')
                ->orderBy('created_at', 'asc')
                ->get()
                ->pluck('mobile_number');
        } catch (QueryException $e) {
            Log::error('Database error fetching mobile numbers', ['lead_id' => $leadId, 'exception' => $e]);
            throw new DomainException('Database error while fetching mobile numbers.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching mobile numbers', ['lead_id' => $leadId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching mobile numbers.');
        }
    }
}

