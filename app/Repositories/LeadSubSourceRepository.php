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

        // 1. Legacy filter: Filter by specific lead source ID
        if (!empty($filters['lead_source_id'])) {
            $query->where('lead_source_id', $filters['lead_source_id']);
        }

        // 2. NEW filter: For general search functionality
        // We're assuming the search will be performed on the 'name' column
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where('name', 'LIKE', "%{$searchTerm}%"); // Replace 'name' with your actual column name if different
        }
        
        // --- End Filter logic ---


        // Apply pagination
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getLeadSubSourceById($id) 
    {
        // Also load the parent 'leadSource' relationship
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

    public function getLeadSubSourcesBySourceId($sourceId)
    {
        return $this->model->where('lead_source_id', $sourceId)->get();
    }
}
