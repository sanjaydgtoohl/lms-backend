<?php

namespace App\Repositories;

use App\Contracts\Repositories\CityRepositoryInterface;
use App\Models\City; // Aapka diya gaya model

class EloquentCityRepository implements CityRepositoryInterface 
{
    protected $model;

    public function __construct(City $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        // Sabhi cities ko unke country aur state ke saath load karein
        return $this->model->with(['country', 'state'])->latest()->get();
    }

    public function getPaginated(int $perPage = 10)
    {
        // Country aur state ke saath load karein
        return $this->model->with(['country', 'state'])->latest()->paginate($perPage);
    }

    public function getByState(int $stateId)
    {
        return $this->model->where('state_id', $stateId)
                           ->with(['country', 'state'])
                           ->latest()
                           ->get();
    }

    public function getByCountry(int $countryId)
    {
        return $this->model->where('country_id', $countryId)
                           ->with(['country', 'state'])
                           ->latest()
                           ->get();
    }

    public function findById(int $id)
    {
        // Country aur state ke saath load karein
        return $this->model->with(['country', 'state'])->findOrFail($id);
    }

    public function create(array $data)
    {
        // Model mein 'name', 'country_id', 'state_id' fillable hain
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $city = $this->model->findOrFail($id);
        $city->update($data);
        return $city;
    }

    public function delete(int $id)
    {
        $city = $this->model->findOrFail($id);
        // Model mein SoftDeletes nahi hai, isliye yeh HARD delete hoga
        return $city->delete(); 
    }
}
