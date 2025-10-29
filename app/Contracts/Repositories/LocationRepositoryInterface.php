<?php

namespace App\Contracts\Repositories;

interface LocationRepositoryInterface
{
    public function getCountries();
    public function getStates($country_id);
    public function getCities($country_id, $state_id);
    
    // Country methods
    public function createCountry($data);
    public function updateCountry($id, $data);
    public function deleteCountry($id);
    
    // State methods
    public function createState($data);
    public function updateState($id, $data);
    public function deleteState($id);
    
    // City methods
    public function createCity($data);
    public function updateCity($id, $data);
    public function deleteCity($id);
}
