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
        // Status field nahi hai, isliye sabko get karein
        return $this->model->latest()->get();
    }

    public function getPaginated(int $perPage = 10)
    {
        // States ke saath load karein
        return $this->model->with('states')->latest()->paginate($perPage);
    }

    public function findById(int $id)
    {
        // States ke saath load karein
        return $this->model->with('states')->findOrFail($id);
    }

    public function create(array $data)
    {
        // Model mein sirf 'name' fillable hai
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
        // Model mein SoftDeletes nahi hai, isliye yeh HARD delete hoga
        return $country->delete(); 
    }
}

