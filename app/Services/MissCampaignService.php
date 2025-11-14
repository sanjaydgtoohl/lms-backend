<?php

namespace App\Services;

use App\Contracts\Repositories\MissCampaignRepositoryInterface;
use App\Models\MissCampaign;
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

            return $this->missCampaignRepository->createMissCampaign($data);
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
            return $this->missCampaignRepository->updateMissCampaign($id, $data);
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
}