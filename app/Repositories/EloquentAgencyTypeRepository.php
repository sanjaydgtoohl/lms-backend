<?php

namespace App\Repositories;

use App\Contracts\Repositories\AgencyTypeRepositoryInterface;
use App\Models\AgencyType;

class EloquentAgencyTypeRepository implements AgencyTypeRepositoryInterface 
{
    protected $model;

    public function __construct(AgencyType $model)
    {
        $this->model = $model;
    }

    public function allActive()
    {
        return $this->model->where('status', '1')->latest()->get();
    }

    public function findById(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $agencyType = $this->model->findOrFail($id);
        $agencyType->update($data);
        return $agencyType;
    }

    public function delete(int $id)
    {
        $agencyType = $this->model->findOrFail($id);
        return $agencyType->delete();
    }
}