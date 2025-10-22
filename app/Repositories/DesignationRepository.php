<?php

namespace App\Repositories;

use App\Contracts\Repositories\DesignationRepositoryInterface;
use App\Models\Designation;

class DesignationRepository implements DesignationRepositoryInterface 
{
    protected $model;

    public function __construct(Designation $designation)
    {
        $this->model = $designation;
    }

    public function getAllDesignations() 
    {
        return $this->model->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getDesignationById($id) 
    {
        return $this->model->findOrFail($id);
    }

    public function createDesignation(array $data) 
    {
        return $this->model->create($data);
    }

    public function slugExists(string $slug, $excludeId = null): bool
    {
        $query = $this->model->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function updateDesignation($id, array $data) 
    {
        $designation = $this->model->findOrFail($id);
        $designation->update($data);
        return $designation;
    }

    public function deleteDesignation($id) 
    {
        $designation = $this->model->findOrFail($id);
        return $designation->delete();
    }
}
