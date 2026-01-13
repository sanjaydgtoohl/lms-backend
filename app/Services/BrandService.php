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

            // Check if brand name already exists
            if (Brand::where('name', $data['name'])->exists()) {
                throw new DomainException('Brand name must be unique. This brand name already exists.');
            }

            // Validate required fields: state, city, zone
            if (empty($data['state_id'])) {
                throw new DomainException('State is required.');
            }
            if (empty($data['city_id'])) {
                throw new DomainException('City is required.');
            }
            if (empty($data['zone_id'])) {
                throw new DomainException('Zone is required.');
            }

            // Extract agency_ids if provided, otherwise use default
            $agencyIds = [];
            if (!empty($data['agency_ids']) && is_array($data['agency_ids'])) {
                $agencyIds = $data['agency_ids'];
                unset($data['agency_ids']);
            } elseif (!empty($data['agency_id'])) {
                $agencyIds = [$data['agency_id']];
                unset($data['agency_id']);
            } else {
                // Attach to default "Direct" agency if none provided
                $agencyIds = [$this->getOrCreateDirectAgency()];
            }

            // Create the brand
            $brand = $this->brandRepository->createBrand($data);

            // Attach agencies to brand with timestamps
            if (!empty($agencyIds)) {
                $attachData = [];
                $now = \Carbon\Carbon::now();
                foreach ($agencyIds as $agencyId) {
                    $attachData[$agencyId] = [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $brand->agencies()->attach($attachData);
            }

            return $brand;
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
            // Get the current brand to compare
            $currentBrand = Brand::withTrashed()->find($id);
            
            if (!$currentBrand) {
                throw new DomainException('Brand not found.');
            }

            // Check if brand name is being updated and if it already exists (excluding current brand)
            if (!empty($data['name'])) {
                // Only check uniqueness if the name is actually different from current
                if ($currentBrand->name !== $data['name']) {
                    if (Brand::where('name', $data['name'])
                        ->where('id', '!=', $id)
                        ->whereNull('deleted_at')
                        ->exists()) {
                        throw new DomainException('Brand name must be unique. This brand name already exists.');
                    }
                }
            }

            // Validate required fields: state, city, zone (if provided)
            if (isset($data['state_id']) && empty($data['state_id'])) {
                throw new DomainException('State is required.');
            }
            if (isset($data['city_id']) && empty($data['city_id'])) {
                throw new DomainException('City is required.');
            }
            if (isset($data['zone_id']) && empty($data['zone_id'])) {
                throw new DomainException('Zone is required.');
            }

            // Extract agency_ids if provided
            $agencyIds = null;
            if (isset($data['agency_ids'])) {
                $agencyIds = $data['agency_ids'];
                unset($data['agency_ids']);
            } elseif (isset($data['agency_id'])) {
                $agencyIds = [$data['agency_id']];
                unset($data['agency_id']);
            }

            // Update the brand
            $updated = $this->brandRepository->updateBrand($id, $data);

            // Sync agencies if provided (with timestamps)
            if ($agencyIds !== null && !empty($agencyIds)) {
                // Get Direct agency ID
                $directAgencyId = $this->getOrCreateDirectAgency();
                
                // Filter out Direct agency from the new agency list
                $validAgencyIds = array_filter($agencyIds, fn($id) => $id != $directAgencyId);
                
                // If there are valid agencies (other than Direct), sync them
                if (!empty($validAgencyIds)) {
                    $syncData = [];
                    $now = \Carbon\Carbon::now();
                    foreach ($validAgencyIds as $agencyId) {
                        $syncData[$agencyId] = [
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    // sync() replaces all relationships - removes old ones and adds new ones
                    $currentBrand->agencies()->sync($syncData);
                }
            }

            return $updated;
        } catch (QueryException $e) {
            Log::error('Database error updating brand', ['id' => $id, 'data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while updating brand.');
        } catch (DomainException $e) {
            // Re-throw domain exceptions
            throw $e;
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