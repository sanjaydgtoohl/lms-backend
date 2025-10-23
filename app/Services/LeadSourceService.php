<?php

namespace App\Services;

use App\Contracts\Repositories\LeadSourceRepositoryInterface;
use Illuminate\Support\Str;

class LeadSourceService
{
    protected $leadSourceRepository;

    public function __construct(LeadSourceRepositoryInterface $leadSourceRepository)
    {
        $this->leadSourceRepository = $leadSourceRepository;
    }

    public function getAllLeadSources()
    {
        return $this->leadSourceRepository->getAllLeadSources();
    }

    public function createNewLeadSource(array $data)
    {
        $slug = $this->createUniqueSlug($data['name']);
        $data['slug'] = $slug;
        $data['status'] = $data['status'] ?? '1';
        
        return $this->leadSourceRepository->createLeadSource($data);
    }

    public function getLeadSource($id)
    {
        return $this->leadSourceRepository->getLeadSourceById($id);
    }

    public function updateLeadSource($id, array $data)
    {
        if (isset($data['name'])) {
            $leadSource = $this->leadSourceRepository->getLeadSourceById($id);
            if ($leadSource->name !== $data['name']) {
                $data['slug'] = $this->createUniqueSlug($data['name'], $id);
            }
        }
        
        return $this->leadSourceRepository->updateLeadSource($id, $data);
    }

    public function deleteLeadSource($id)
    {
        return $this->leadSourceRepository->deleteLeadSource($id);
    }

    private function createUniqueSlug(string $name, $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        $existing = $this->leadSourceRepository->findBySlug($slug);

        while ($existing && $existing->id != $excludeId) {
            $slug = $originalSlug . '-' . $count++;
            $existing = $this->leadSourceRepository->findBySlug($slug);
        }

        return $slug;
    }
}
