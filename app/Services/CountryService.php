<?php

namespace App\Services;

use App\Contracts\Repositories\CountryRepositoryInterface;

class CountryService
{
    protected $countries;

    public function __construct(CountryRepositoryInterface $countries)
    {
        $this->countries = $countries;
    }

    public function getAllCountries()
    {
        return $this->countries->getAll();
    }

    public function getPaginatedCountries(int $perPage = 10)
    {
        return $this->countries->getPaginated($perPage);
    }

    public function getCountryById(int $id)
    {
        return $this->countries->findById($id);
    }

    public function createCountry(array $data)
    {
        return $this->countries->create($data);
    }

    public function updateCountry(int $id, array $data)
    {
        return $this->countries->update($id, $data);
    }

    public function deleteCountry(int $id)
    {
        return $this->countries->delete($id);
    }
}


