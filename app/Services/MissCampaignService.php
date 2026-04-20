<?php

/**
 * MissCampaign Service
 * -----------------------------------------
 * Provides business logic for miss campaign operations, including CRUD, validation, and media handling.
 *
 * @package App\Services
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Services;

use App\Contracts\Repositories\MissCampaignRepositoryInterface;
use App\Models\MissCampaign;
use App\Events\MissCampaignAssignedEvent;
use DomainException;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MissCampaignService
{
    /**
     * @var MissCampaignRepositoryInterface
     */
    protected MissCampaignRepositoryInterface $missCampaignRepository;

    /**
     * Create a new MissCampaignService instance.
     *
     * @param MissCampaignRepositoryInterface $missCampaignRepository
     */
    public function __construct(MissCampaignRepositoryInterface $missCampaignRepository)
    {
        $this->missCampaignRepository = $missCampaignRepository;
    }

    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Get all miss campaigns with pagination.
     *
     * @param int $perPage
     * @param string|null $searchTerm
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getAllMissCampaigns(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        try {
            return $this->missCampaignRepository->getAllMissCampaigns($perPage, $searchTerm);
        } catch (QueryException $e) {
            Log::error('Database error fetching miss campaigns', ['exception' => $e]);
            throw new DomainException('Database error while fetching miss campaigns.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching miss campaigns', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching miss campaigns.');
        }
    }

    /**
     * Get a simple list of miss campaigns (ID and Name).
     *
     * @return Collection|null
     * @throws DomainException
     */
    public function getMissCampaignList(): ?Collection
    {
        try {
            return $this->missCampaignRepository->getMissCampaignList();
        } catch (QueryException $e) {
            Log::error('Database error fetching miss campaign list', ['exception' => $e]);
            throw new DomainException('Database error while fetching miss campaign list.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching miss campaign list', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching miss campaign list.');
        }
    }

    /**
     * Get a miss campaign by ID.
     *
     * @param int $id
     * @return MissCampaign|null
     * @throws DomainException
     */
    public function getMissCampaign(int $id): ?MissCampaign
    {
        try {
            return $this->missCampaignRepository->getMissCampaignById($id);
        } catch (QueryException $e) {
            Log::error('Database error fetching miss campaign', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while fetching miss campaign.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching miss campaign', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching miss campaign.');
        }
    }

    /**
     * Get a miss campaign by slug.
     *
     * @param string $slug
     * @return MissCampaign|null
     * @throws DomainException
     */
    public function getMissCampaignBySlug(string $slug): ?MissCampaign
    {
        try {
            return $this->missCampaignRepository->getMissCampaignBySlug($slug);
        } catch (QueryException $e) {
            Log::error('Database error fetching miss campaign by slug', ['slug' => $slug, 'exception' => $e]);
            throw new DomainException('Database error while fetching miss campaign by slug.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching miss campaign by slug', ['slug' => $slug, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching miss campaign by slug.');
        }
    }

    // ============================================================================
    // MEDIA OPERATIONS
    // ============================================================================

    /**
     * Upload and store an image for a miss campaign.
     *
     * @param UploadedFile $file
     * @return array|null
     * @throws DomainException
     */
    public function uploadImage(UploadedFile $file): ?array
    {
        try {
            $model = new MissCampaign();
            // Use HandlesFileUploads trait method
            return $model->uploadImage($file, 'miss-campaigns', [
                'disk' => 'public',
                'prefix' => 'campaign_',
                'sizeLimit' => 51200, // 50MB in KB
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error uploading miss campaign image', ['errors' => $e->errors()]);
            throw new DomainException('Invalid image file: ' . implode(', ', array_merge(...array_values($e->errors()))));
        } catch (Exception $e) {
            Log::error('Error uploading miss campaign image', ['exception' => $e]);
            throw new DomainException('Error uploading image: ' . $e->getMessage());
        }
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new miss campaign.
     *
     * @param array $data
     * @return MissCampaign
     * @throws DomainException
     */
    public function createMissCampaign(array $data): MissCampaign
    {
        try {
            if (empty($data['name'])) {
                throw new DomainException('Campaign name is required.');
            }

            // Generate slug if not provided
            if (!isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
            }

            $campaign = $this->missCampaignRepository->createMissCampaign($data);
            
            // Dispatch event if assign_to is provided during creation
            if (isset($data['assign_to']) && !empty($data['assign_to'])) {
                Log::info('Dispatching MissCampaignAssignedEvent on create', [
                    'campaign_id' => $campaign->id,
                    'user_id' => $data['assign_to']
                ]);
                event(new MissCampaignAssignedEvent($campaign->id, $data['assign_to']));
            }
            
            return $campaign;
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error creating miss campaign', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while creating miss campaign.');
        } catch (Exception $e) {
            Log::error('Unexpected error creating miss campaign', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while creating miss campaign.');
        }
    }

    /**
     * Update an existing miss campaign.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws DomainException
     */
    public function updateMissCampaign(int $id, array $data): bool
    {
        try {
            // Get the current campaign to check if assign_to is changing
            $campaign = $this->missCampaignRepository->getMissCampaignById($id);
            $oldAssignTo = $campaign ? $campaign->assign_to : null;
            
            $updated = $this->missCampaignRepository->updateMissCampaign($id, $data);
            
            // Dispatch event if assign_to is being updated and is not empty
            if ($updated && isset($data['assign_to']) && !empty($data['assign_to']) && $data['assign_to'] != $oldAssignTo) {
                Log::info('Dispatching MissCampaignAssignedEvent on update', [
                    'campaign_id' => $id,
                    'user_id' => $data['assign_to'],
                    'old_assign_to' => $oldAssignTo
                ]);
                event(new MissCampaignAssignedEvent($id, $data['assign_to']));
            }
            
            return $updated;
        } catch (QueryException $e) {
            Log::error('Database error updating miss campaign', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while updating miss campaign.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating miss campaign', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating miss campaign.');
        }
    }

    /**
     * Delete a miss campaign (soft delete).
     *
     * @param int $id
     * @return bool
     * @throws DomainException
     */
    public function deleteMissCampaign(int $id): bool
    {
        try {
            return $this->missCampaignRepository->deleteMissCampaign($id);
        } catch (QueryException $e) {
            Log::error('Database error deleting miss campaign', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while deleting miss campaign.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting miss campaign', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while deleting miss campaign.');
        }
    }

    /**
     * Update the status of a miss campaign.
     *
     * @param int $id
     * @param string $status
     * @return bool
     * @throws DomainException
     */
    public function updateStatusMissCampaign(int $id, string $status): bool
    {
        try {
            if (empty($status)) {
                throw new DomainException('Status is required.');
            }

            // Validate status values (1 = active, 2 = inactive, 15 = completed)
            $validStatuses = ['1', '2', '15'];
            if (!in_array($status, $validStatuses)) {
                throw new DomainException('Invalid status value. Allowed values: 1, 2, 15');
            }

            return $this->missCampaignRepository->updateStatus($id, $status);
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error updating miss campaign status', ['id' => $id, 'status' => $status, 'exception' => $e]);
            throw new DomainException('Database error while updating miss campaign status.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating miss campaign status', ['id' => $id, 'status' => $status, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating miss campaign status.');
        }
    }

    /**
     * Assign a user to a miss campaign.
     *
     * @param int $id
     * @param int $userId
     * @param int $assignBy
     * @return bool
     * @throws DomainException
     */
    public function assignUserToMissCampaign(int $id, int $userId, int $assignBy): bool
    {
        try {
            if (empty($userId)) {
                throw new DomainException('User ID is required.');
            }

            if (empty($assignBy)) {
                throw new DomainException('Assign by user ID is required.');
            }

            // Fetch current assigned user before update
            $campaign = $this->missCampaignRepository->getMissCampaignById($id);
            $currentAssignedUser = $campaign ? $campaign->assign_to : null;

            $result = $this->missCampaignRepository->assignUser($id, $userId, $assignBy);
            
            // Dispatch event only if assignment successful and user changed
            if ($result && $currentAssignedUser != $userId) {
                Log::info('Dispatching MissCampaignAssignedEvent on assign', [
                    'campaign_id' => $id,
                    'user_id' => $userId,
                    'assigned_by' => $assignBy
                ]);
                event(new MissCampaignAssignedEvent($id, $userId));
            }
            
            return $result;
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error assigning user to miss campaign', ['id' => $id, 'userId' => $userId, 'assignBy' => $assignBy, 'exception' => $e]);
            throw new DomainException('Database error while assigning user to miss campaign.');
        } catch (Exception $e) {
            Log::error('Unexpected error assigning user to miss campaign', ['id' => $id, 'userId' => $userId, 'assignBy' => $assignBy, 'exception' => $e]);
            throw new DomainException('Unexpected error while assigning user to miss campaign.');
        }
    }

    // ============================================================================
    // AUTHORIZATION OPERATIONS
    // ============================================================================

    /**
     * Check if the authenticated user can view assignment fields for a miss campaign.
     * 
     * Permission granted to:
     * - Super Admin role
     * - User who assigned the campaign (assign_by)
     * - User who is assigned to the campaign (assign_to)
     *
     * @param MissCampaign $campaign
     * @param \App\Models\User $user
     * @return bool
     */
    public function canViewAssignmentFields(MissCampaign $campaign, \App\Models\User $user): bool
    {
        // Super Admin can always view assignment fields
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // User who assigned this campaign can view
        if ($campaign->assign_by && $campaign->assign_by == $user->id) {
            return true;
        }

        // User who is assigned to this campaign can view
        if ($campaign->assign_to && $campaign->assign_to == $user->id) {
            return true;
        }

        return false;
    }
}