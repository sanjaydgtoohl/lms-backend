<?php

namespace App\Services;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Agency;
use App\Models\AgencyType;
use App\Models\Brand;
use DomainException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class BrandService
{
    /**
     * @var BrandRepositoryInterface
     */
    protected BrandRepositoryInterface $brandRepository;

    /**
     * Create a new BrandService instance.
     *
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Get all brands with pagination.
     *
     * @param int $perPage
     * @param string|null $searchTerm
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getAllBrands(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        try {
            return $this->brandRepository->getAllBrands($perPage, $searchTerm);
        } catch (QueryException $e) {
            Log::error('Database error fetching brands', ['exception' => $e]);
            throw new DomainException('Database error while fetching brands.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching brands', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching brands.');
        }
    }

    /**
     * Get a brand by ID.
     *
     * @param int $id
     * @return Brand|null
     * @throws DomainException
     */
    public function getBrand(int $id): ?Brand
    {
        try {
            return $this->brandRepository->getBrandById($id);
        } catch (QueryException $e) {
            Log::error('Database error fetching brand', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while fetching brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching brand', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching brand.');
        }
    }

    /**
     * Get a brand by slug.
     *
     * @param string $slug
     * @return Brand|null
     * @throws DomainException
     */
    public function getBrandBySlug(string $slug): ?Brand
    {
        try {
            return $this->brandRepository->getBrandBySlug($slug);
        } catch (QueryException $e) {
            Log::error('Database error fetching brand by slug', ['slug' => $slug, 'exception' => $e]);
            throw new DomainException('Database error while fetching brand by slug.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching brand by slug', ['slug' => $slug, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching brand by slug.');
        }
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new brand.
     *
     * @param array $data
     * @return Brand
     * @throws DomainException
     */
    public function createBrand(array $data): Brand
    {
        try {
            if (empty($data['name'])) {
                throw new DomainException('Brand name is required.');
            }

            // Attach to default "Direct" agency if none provided
            if (empty($data['agency_id'])) {
                $data['agency_id'] = $this->getOrCreateDirectAgency();
            }
            return $this->brandRepository->createBrand($data);
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error creating brand', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while creating brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error creating brand', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while creating brand.');
        }
    }

    /**
     * Update an existing brand.
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws DomainException
     */
    public function updateBrand(int $id, array $data): bool
    {
        try {
            return $this->brandRepository->updateBrand($id, $data);
        } catch (QueryException $e) {
            Log::error('Database error updating brand', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while updating brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating brand', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating brand.');
        }
    }

    /**
     * Delete a brand (soft delete).
     *
     * @param int $id
     * @return bool
     * @throws DomainException
     */
    public function deleteBrand(int $id): bool
    {
        try {
            return $this->brandRepository->deleteBrand($id);
        } catch (QueryException $e) {
            Log::error('Database error deleting brand', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while deleting brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting brand', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while deleting brand.');
        }
    }

    

    // ============================================================================
    // PRIVATE HELPER METHODS
    // ============================================================================

    /**
     * Get or create a default "Direct" agency.
     *
     * @return int
     */
    private function getOrCreateDirectAgency(): int
    {
        $directAgency = Agency::withTrashed()
            ->whereRaw('LOWER(name) = ?', ['direct'])
            ->first();

        if (!$directAgency) {
            $defaultAgencyType = $this->getOrCreateDefaultAgencyType();
            
            $directAgency = Agency::create([
                'name' => 'Direct',
                'slug' => 'direct',
                'status' => '1',
                'agency_type' => $defaultAgencyType->id,
            ]);
        } elseif ($directAgency->trashed()) {
            $directAgency->restore();
        }

        return $directAgency->id;
    }

    /**
     * Get or create a default agency type.
     *
     * @return AgencyType
     */
    private function getOrCreateDefaultAgencyType(): AgencyType
    {
        $defaultAgencyType = AgencyType::withTrashed()
            ->whereRaw('LOWER(slug) = ?', ['online'])
            ->first();

        if (!$defaultAgencyType) {
            $defaultAgencyType = AgencyType::withTrashed()->first();
        }

        if (!$defaultAgencyType) {
            $defaultAgencyType = AgencyType::create([
                'name' => 'Online',
                'slug' => 'online',
                'status' => 1,
            ]);
        } elseif ($defaultAgencyType->trashed()) {
            $defaultAgencyType->restore();
        }

        return $defaultAgencyType;
    }

    /**
     * Get a simple list of brands (ID and Name).
     *
     * @return \Illuminate\Support\Collection|null
     * @throws DomainException
     */
    public function getBrandList(): ?\Illuminate\Support\Collection
    {
        try {
            // Hum repository se list maangenge
            return $this->brandRepository->getBrandList();
        } catch (QueryException $e) {
            Log::error('Database error fetching brand list', ['exception' => $e]);
            throw new DomainException('Database error while fetching brand list.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching brand list', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching brand list.');
        }
    }
}