<?php

namespace App\Services;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Agency;
use App\Models\AgencyType;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Exception;

class BrandService
{
    protected $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function getAllBrands(int $perPage = 10)
    {
        try {
            return $this->brandRepository->getAllBrands($perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching brands: ' . $e->getMessage());
            throw new DomainException('Database error while fetching brands.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching brands: ' . $e->getMessage());
            throw new DomainException('Unexpected error while fetching brands.');
        }
    }

    public function getBrand($id)
    {
        try {
            return $this->brandRepository->getBrandById($id);
        } catch (QueryException $e) {
            Log::error('Database error fetching brand: ' . $e->getMessage());
            throw new DomainException('Database error while fetching brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching brand: ' . $e->getMessage());
            throw new DomainException('Brand not found.');
        }
    }

    public function createBrand(array $data)
    {
        try {
            if (empty($data['name'])) {
                throw new DomainException('Brand name is required.');
            }

            // If no agency selected, attach to a default "Direct" agency
            if (empty($data['agency_id'])) {
                $directAgency = Agency::withTrashed()
                    ->whereRaw('LOWER(name) = ?', ['direct'])
                    ->first();

                if (!$directAgency) {
                    // Determine a default agency type (prefer 'online', else first, else create)
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

                    $directAgency = Agency::create([
                        'name' => 'Direct',
                        'slug' => 'direct',
                        'status' => '1',
                        'agency_type_id' => $defaultAgencyType->id,
                    ]);
                } elseif ($directAgency->trashed()) {
                    $directAgency->restore();
                }

                $data['agency_id'] = $directAgency->id;
            }
            return $this->brandRepository->createBrand($data);
        } catch (QueryException $e) {
            Log::error('Database error creating brand: ' . $e->getMessage());
            throw new DomainException('Database error while creating brand.');
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Unexpected error creating brand: ' . $e->getMessage());
            throw new DomainException('Unexpected error while creating brand.');
        }
    }

    public function updateBrand($id, array $data)
    {
        try {
            return $this->brandRepository->updateBrand($id, $data);
        } catch (QueryException $e) {
            Log::error('Database error updating brand: ' . $e->getMessage());
            throw new DomainException('Database error while updating brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating brand: ' . $e->getMessage());
            throw new DomainException('Unexpected error while updating brand.');
        }
    }

    public function deleteBrand($id)
    {
        try {
            return $this->brandRepository->deleteBrand($id);
        } catch (QueryException $e) {
            Log::error('Database error deleting brand: ' . $e->getMessage());
            throw new DomainException('Database error while deleting brand.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting brand: ' . $e->getMessage());
            throw new DomainException('Unexpected error while deleting brand.');
        }
    }
}


