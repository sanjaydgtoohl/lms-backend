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

    public function getAllLeadSubSources(array $filters = []) 
    {
        $query = $this->model->with('leadSource'); // Parent ko saath mein load karein

        // Filter logic: Agar lead_source_id diya hai, toh filter karein
        if (!empty($filters['lead_source_id'])) {
            $query->where('lead_source_id', $filters['lead_source_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
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
