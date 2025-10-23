<?php

namespace App\Repositories;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Models\Department;

class DepartmentRepository implements DepartmentRepositoryInterface 
{
    protected $model;

    public function __construct(Department $department)
    {
        $this->model = $department;
    }

    public function getAllDepartments() 
    {
        return $this->model->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getDepartmentById($id) 
    {
        return $this->model->findOrFail($id);
    }

    public function createDepartment(array $data) 
    {
        return $this->model->create($data);
    }

    public function updateDepartment($id, array $data) 
    {
        $department = $this->model->findOrFail($id);
        $department->update($data);
        return $department;
    }

    public function deleteDepartment($id) 
    {
        $department = $this->model->findOrFail($id);
        return $department->delete();
    }

    /**
     * NAYA METHOD IMPLEMENT KIYA GAYA
     */
    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}