<?php

namespace App\Services;

use App\Repositories\LocationRepository;

class LocationService
{
    protected $repo;

    public function __construct(LocationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function countries()
    {
        return $this->repo->getCountries();
    }

    public function states($country_id)
    {
        return $this->repo->getStates($country_id);
    }

    public function cities($country_id, $state_id)
    {
        return $this->repo->getCities($country_id, $state_id);
    }
    
    // Country methods
    public function createCountry($data)
    {
        return $this->repo->createCountry($data);
    }
    
    public function updateCountry($id, $data)
    {
        return $this->repo->updateCountry($id, $data);
    }
    
    public function deleteCountry($id)
    {
        return $this->repo->deleteCountry($id);
    }
    
    // State methods
    public function createState($data)
    {
        return $this->repo->createState($data);
    }
    
    public function updateState($id, $data)
    {
        return $this->repo->updateState($id, $data);
    }
    
    public function deleteState($id)
    {
        return $this->repo->deleteState($id);
    }
    
    // City methods
    public function createCity($data)
    {
        return $this->repo->createCity($data);
    }
    
    public function updateCity($id, $data)
    {
        return $this->repo->updateCity($id, $data);
    }
    
    public function deleteCity($id)
    {
        return $this->repo->deleteCity($id);
    }
}
