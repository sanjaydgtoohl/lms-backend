<?php

namespace App\Repositories;

use App\Contracts\Repositories\AgencyRepositoryInterface;
use App\Models\Agency;
use App\Models\AgencyGroup;
use App\Models\AgencyType;
use App\Models\Brand;

class EloquentAgencyRepository implements AgencyRepositoryInterface 
{
    protected $model;
    protected $agencyGroupModel;
    protected $agencyTypeModel;
    protected $brandModel;

    public function __construct(Agency $model, AgencyGroup $agencyGroup, AgencyType $agencyType, Brand $brand)
    {
        $this->model = $model;
        $this->agencyGroupModel = $agencyGroup;
        $this->agencyTypeModel = $agencyType;
        $this->brandModel = $brand;
    }

    public function getPaginated(int $perPage = 10)
    {
        return $this->model->with(['agencyGroup', 'agencyType', 'brand'])
                           ->latest()
                           ->paginate($perPage);
    }

    public function getCreateDependencies()
    {
        return [
            'agency_groups' => $this->agencyGroupModel->where('status', '1')->get(['id', 'name']),
            'agency_types' => $this->agencyTypeModel->where('status', '1')->get(['id', 'name']),
            'brands' => $this->brandModel->where('status', '1')->get(['id', 'name'])
        ];
    }

    public function findById(int $id)
    {
        return $this->model->with(['agencyGroup', 'agencyType', 'brand'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $agency = $this->model->findOrFail($id);
        $agency->update($data);
        return $agency;
    }

    public function delete(int $id)
    {
        $agency = $this->model->findOrFail($id);
        return $agency->delete();
    }
}