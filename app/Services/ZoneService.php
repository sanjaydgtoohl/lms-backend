<?php

namespace App\Services;

use App\Models\Zone;
use App\Contracts\Repositories\ZoneRepositoryInterface; // Model ki jagah Interface import karein
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Database\QueryException;

class ZoneService
{
    protected $zoneRepository;

    // Interface ko constructor mein inject karein
    public function __construct(ZoneRepositoryInterface $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }
    /**
     * @param int $perPage
     * @param string|null $searchTerm
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getAllZones(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator 
    {
        try {
            // Logic ko repository se call karein, $searchTerm bhi pass karein
            return $this->zoneRepository->allPaginated($perPage, $searchTerm);
        } catch (QueryException $e) {
            // Database error ke liye exception throw karein
            throw new Exception('Database error while fetching zones: ' . $e->getMessage());
        } catch (Exception $e) {
            // Any other error
            throw new Exception('Unexpected error while fetching zones: ' . $e->getMessage());
        }
    }

    public function getActiveZonesList()
    {
        return $this->zoneRepository->getActiveList();
    }

    public function getZoneById($id)
    {
        return $this->zoneRepository->findById($id);
    }

    public function createZone(array $data)
    {
        // Business logic (jaise slug banana) service mein rehta hai
        $dataToSave = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'status' => $data['status'] ?? '1',
        ];
        
        // Data ko repository ko pass karein
        return $this->zoneRepository->create($dataToSave);
    }

    public function updateZone(Zone $zone, array $data)
    {
        // Business logic
        $dataToUpdate = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'status' => $data['status'],
        ];

        return $this->zoneRepository->update($zone, $dataToUpdate);
    }

    public function deleteZone(Zone $zone)
    {
        return $this->zoneRepository->delete($zone);
    }
}