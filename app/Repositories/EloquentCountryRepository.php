<?php

namespace App\Repositories;

use App\Contracts\Repositories\CountryRepositoryInterface;
use App\Models\Country;

class EloquentCountryRepository implements CountryRepositoryInterface 
{
    protected $model;

    public function __construct(Country $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        // No status field exists, so get all records
        return $this->model->latest()->get();
    }

    public function getPaginated(int $perPage = 10)
    {
        // Load with states relationship
        return $this->model->with('states')->latest()->paginate($perPage);
    }

    public function findById(int $id)
    {
        // Load with states relationship
        return $this->model->with('states')->findOrFail($id);
    }

    public function create(array $data)
    {
        // Only 'name' field is fillable in the model
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $country = $this->model->findOrFail($id);
        $country->update($data);
        return $country;
    }

    public function delete(int $id)
    {
        $country = $this->model->findOrFail($id);
        // This will be a HARD delete since model doesn't use SoftDeletes
        return $country->delete(); 
    }
}

