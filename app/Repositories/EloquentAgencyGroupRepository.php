<?php

namespace App\Repositories;

use App\Contracts\Repositories\AgencyGroupRepositoryInterface;
use App\Models\AgencyGroup;

class EloquentAgencyGroupRepository implements AgencyGroupRepositoryInterface 
{
    protected $model;

    public function __construct(AgencyGroup $model)
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
        $agencyGroup = $this->model->findOrFail($id);
        $agencyGroup->update($data);
        return $agencyGroup;
    }

    public function delete(int $id)
    {
        $agencyGroup = $this->model->findOrFail($id);
        return $agencyGroup->delete();
    }
}