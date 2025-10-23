<?php

namespace App\Repositories;

use App\Contracts\Repositories\LeadSourceRepositoryInterface;
use App\Models\LeadSource;

class LeadSourceRepository implements LeadSourceRepositoryInterface 
{
    protected $model;

    public function __construct(LeadSource $leadSource)
    {
        $this->model = $leadSource;
    }

    public function getAllLeadSources() 
    {
        return $this->model->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getLeadSourceById($id) 
    {
        return $this->model->findOrFail($id);
    }

    public function createLeadSource(array $data) 
    {
        return $this->model->create($data);
    }

    public function updateLeadSource($id, array $data) 
    {
        $leadSource = $this->model->findOrFail($id);
        $leadSource->update($data);
        return $leadSource;
    }

    public function deleteLeadSource($id) 
    {
        $leadSource = $this->model->findOrFail($id);
        return $leadSource->delete();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
