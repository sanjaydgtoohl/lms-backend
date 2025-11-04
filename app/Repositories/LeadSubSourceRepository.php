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

    public function getAllLeadSubSources(array $filters = [], int $perPage = 10,)
    {
        $query = $this->model->with('leadSource'); 

        // --- Filter logic ---

        // 1. Puraana filter: Specific ID se
        if (!empty($filters['lead_source_id'])) {
            $query->where('lead_source_id', $filters['lead_source_id']);
        }

        // 2. NAYA filter: General search ke liye
        // Hum maan rahe hain ki search 'name' column par hoga
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where('name', 'LIKE', "%{$searchTerm}%"); // 'name' ko apne column naam se badlein
        }
        
        // --- End Filter logic ---


        // Apply pagination
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
