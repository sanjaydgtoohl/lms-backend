<?php

namespace App\Repositories;

use App\Contracts\Repositories\LeadSubSourceRepositoryInterface;
use App\Models\LeadSubSource;

class LeadSubSourceRepository implements LeadSubSourceRepositoryInterface 
{
    protected $model;

    public function __construct(LeadSubSource $leadSubSource)
    {
        $this->model = $leadSubSource;
    }

    public function getAllLeadSubSources(array $filters = [], int $perPage = 10) // <-- perPage added
    {
        $query = $this->model->with('leadSource'); 

        // Filter logic
        if (!empty($filters['lead_source_id'])) {
            $query->where('lead_source_id', $filters['lead_source_id']);
        }

        // Apply pagination using the provided $perPage value
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getLeadSubSourceById($id) 
    {
        // Parent 'leadSource' ko bhi load karein
        return $this->model->with('leadSource')->findOrFail($id);
    }

    public function createLeadSubSource(array $data) 
    {
        return $this->model->create($data);
    }

    public function updateLeadSubSource($id, array $data) 
    {
        $leadSubSource = $this->model->findOrFail($id);
        $leadSubSource->update($data);
        return $leadSubSource;
    }

    public function deleteLeadSubSource($id) 
    {
        $leadSubSource = $this->model->findOrFail($id);
        return $leadSubSource->delete();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
