<?php

namespace App\Repositories;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class LocationRepository implements LocationRepositoryInterface
{
    public function getCountries()
    {
        return Country::select('id', 'name')->get();
    }

    public function getStates($country_id)
    {
        return State::where('country_id', $country_id)
                    ->select('id', 'name')
                    ->get();
    }

    public function getCities($country_id, $state_id)
    {
        return City::where('country_id', $country_id)
                    ->where('state_id', $state_id)
                    ->select('id', 'name')
                    ->get();
    }
    
    // Country methods
    public function createCountry($data)
    {
        return Country::create($data);
    }
    
    public function updateCountry($id, $data)
    {
        $country = Country::findOrFail($id);
        $country->update($data);
        return $country;
    }
    
    public function deleteCountry($id)
    {
        return Country::findOrFail($id)->delete();
    }
    
    // State methods
    public function createState($data)
    {
        return State::create($data);
    }
    
    public function updateState($id, $data)
    {
        $state = State::findOrFail($id);
        $state->update($data);
        return $state;
    }
    
    public function deleteState($id)
    {
        return State::findOrFail($id)->delete();
    }
    
    // City methods
    public function createCity($data)
    {
        return City::create($data);
    }
    
    public function updateCity($id, $data)
    {
        $city = City::findOrFail($id);
        $city->update($data);
        return $city;
    }
    
    public function deleteCity($id)
    {
        return City::findOrFail($id)->delete();
    }
}
