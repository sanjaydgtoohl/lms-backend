<?php

namespace App\Services;

use App\Contracts\Repositories\LeadSubSourceRepositoryInterface; // <-- Naya Namespace
use Illuminate\Support\Str;

class LeadSubSourceService
{
    protected $leadSubSourceRepository;

    public function __construct(LeadSubSourceRepositoryInterface $leadSubSourceRepository)
    {
        $this->leadSubSourceRepository = $leadSubSourceRepository;
    }

    public function getAllLeadSubSources(array $filters = [])
    {
        return $this->leadSubSourceRepository->getAllLeadSubSources($filters);
    }

    public function createNewLeadSubSource(array $data)
    {
        // 'name' se slug banayein
        $slug = $this->createUniqueSlug($data['name']);
        $data['slug'] = $slug;
        $data['status'] = $data['status'] ?? '1';
        
        $subSource = $this->leadSubSourceRepository->createLeadSubSource($data);
        return $subSource->load('leadSource'); // Parent ko load karke return karein
    }

    public function getLeadSubSource($id)
    {
        return $this->leadSubSourceRepository->getLeadSubSourceById($id);
    }

    public function updateLeadSubSource($id, array $data)
    {
        if (isset($data['name'])) {
            $leadSubSource = $this->leadSubSourceRepository->getLeadSubSourceById($id);
            if ($leadSubSource->name !== $data['name']) {
                $data['slug'] = $this->createUniqueSlug($data['name'], $id);
            }
        }
        
        $updated = $this->leadSubSourceRepository->updateLeadSubSource($id, $data);
        return $updated->load('leadSource'); // Parent ko load karke return karein
    }

    public function deleteLeadSubSource($id)
    {
        return $this->leadSubSourceRepository->deleteLeadSubSource($id);
    }

    // Unique slug helper
    private function createUniqueSlug(string $name, $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        $existing = $this->leadSubSourceRepository->findBySlug($slug);

        while ($existing && $existing->id != $excludeId) {
            $slug = $originalSlug . '-' . $count++;
            $existing = $this->leadSubSourceRepository->findBySlug($slug);
        }

        return $slug;
    }
}
