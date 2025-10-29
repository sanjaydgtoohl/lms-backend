<?php

namespace App\Repositories;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;

class BrandRepository implements BrandRepositoryInterface
{
    protected $model;

    public function __construct(Brand $brand)
    {
        $this->model = $brand;
    }

    public function getAllBrands(int $perPage = 10)
    {
        return $this->model
            ->with(['agency','brandType','contactPerson','industry','country','state','city','region','subregions'])
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getBrandById($id)
    {
        return $this->model
            ->with(['agency','brandType','contactPerson','industry','country','state','city','region','subregions'])
            ->findOrFail($id);
    }

    public function createBrand(array $data)
    {
        return $this->model->create($data);
    }

    public function updateBrand($id, array $data)
    {
        $brand = $this->model->findOrFail($id);
        $brand->update($data);
        return $brand;
    }

    public function deleteBrand($id)
    {
        $brand = $this->model->findOrFail($id);
        return $brand->delete();
    }
}


