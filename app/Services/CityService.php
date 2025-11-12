<?php

namespace App\Services;

use App\Contracts\Repositories\CityRepositoryInterface;

class CityService
{
    protected $cityRepo;

    public function __construct(CityRepositoryInterface $cityRepo)
    {
        $this->cityRepo = $cityRepo;
    }

    public function getAllCities()
    {
        return $this->cityRepo->getAll();
    }

    public function getPaginatedCities()
    {
        return $this->cityRepo->getPaginated(10);
    }

    public function getCitiesByState(int $stateId)
    {
        return $this->cityRepo->getByState($stateId);
    }

    public function getCitiesByCountry(int $countryId)
    {
        return $this->cityRepo->getByCountry($countryId);
    }

    public function getCityById(int $id)
    {
        return $this->cityRepo->findById($id);
    }

    public function createCity(array $data)
    {
        // Direct create without additional business logic
        return $this->cityRepo->create($data);
    }

    public function updateCity(int $id, array $data)
    {
        // Direct update without additional business logic
        return $this->cityRepo->update($id, $data);
    }

    public function deleteCity(int $id)
    {
        return $this->cityRepo->delete($id);
    }
}
