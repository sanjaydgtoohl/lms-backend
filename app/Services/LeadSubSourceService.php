<?php

namespace App\Services;

use App\Contracts\Repositories\LeadSubSourceRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;

class LeadSubSourceService
{
    protected $leadSubSourceRepository;

    public function __construct(LeadSubSourceRepositoryInterface $leadSubSourceRepository)
    {
        $this->leadSubSourceRepository = $leadSubSourceRepository;
    }

    /**
     * Fetch all lead sub-sources with optional filters
     */
    public function getAllLeadSubSources(array $filters = [])
    {
        try {
            return $this->leadSubSourceRepository->getAllLeadSubSources($filters);
        } catch (QueryException $e) {
            throw new Exception('Database query failed while fetching lead sub-sources: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('An unexpected error occurred while fetching lead sub-sources: ' . $e->getMessage());
        }
    }

    /**
     * Create a new lead sub-source
     */
    public function createNewLeadSubSource(array $data)
    {
        try {
            $slug = $this->createUniqueSlug($data['name']);
            $data['slug'] = $slug;
            $data['status'] = $data['status'] ?? '1';

            $subSource = $this->leadSubSourceRepository->createLeadSubSource($data);

            return $subSource->load('leadSource');
        } catch (QueryException $e) {
            throw new Exception('Failed to create lead sub-source: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('An unexpected error occurred while creating lead sub-source: ' . $e->getMessage());
        }
    }

    /**
     * Fetch a single lead sub-source by ID
     */
    public function getLeadSubSource($id)
    {
        try {
            return $this->leadSubSourceRepository->getLeadSubSourceById($id);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Lead sub-source not found for the given ID.');
        } catch (Exception $e) {
            throw new Exception('An unexpected error occurred while fetching the lead sub-source: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing lead sub-source
     */
    public function updateLeadSubSource($id, array $data)
    {
        try {
            if (isset($data['name'])) {
                $leadSubSource = $this->leadSubSourceRepository->getLeadSubSourceById($id);

                if ($leadSubSource->name !== $data['name']) {
                    $data['slug'] = $this->createUniqueSlug($data['name'], $id);
                }
            }

            $updated = $this->leadSubSourceRepository->updateLeadSubSource($id, $data);

            return $updated->load('leadSource');
        } catch (ModelNotFoundException $e) {
            throw new Exception('Lead sub-source not found for update.');
        } catch (QueryException $e) {
            throw new Exception('Failed to update lead sub-source: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('An unexpected error occurred while updating lead sub-source: ' . $e->getMessage());
        }
    }

    /**
     * Delete a lead sub-source
     */
    public function deleteLeadSubSource($id)
    {
        try {
            return $this->leadSubSourceRepository->deleteLeadSubSource($id);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Lead sub-source not found for deletion.');
        } catch (QueryException $e) {
            throw new Exception('Failed to delete lead sub-source: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('An unexpected error occurred while deleting lead sub-source: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique slug for the lead sub-source name
     */
    private function createUniqueSlug(string $name, $excludeId = null): string
    {
        try {
            $slug = Str::slug($name);
            $originalSlug = $slug;
            $count = 1;

            $existing = $this->leadSubSourceRepository->findBySlug($slug);

            while ($existing && $existing->id != $excludeId) {
                $slug = $originalSlug . '-' . $count++;
                $existing = $this->leadSubSourceRepository->findBySlug($slug);
            }

            return $slug;
        } catch (QueryException $e) {
            throw new Exception('Error generating unique slug: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Unexpected error while generating slug: ' . $e->getMessage());
        }
    }
}
