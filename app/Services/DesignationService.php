<?php

namespace App\Services;

use App\Contracts\Repositories\DesignationRepositoryInterface;
use Illuminate\Support\Str;
use DomainException;

class DesignationService
{
    protected $designationRepository;

    public function __construct(DesignationRepositoryInterface $designationRepository)
    {
        $this->designationRepository = $designationRepository;
    }

    public function getAllDesignations()
    {
        return $this->designationRepository->getAllDesignations();
    }

    public function createNewDesignation(array $data)
    {
        // Normalize input keys: accept 'designation_name' or 'title'
        if (isset($data['designation_name']) && !isset($data['title'])) {
            $data['title'] = $data['designation_name'];
            unset($data['designation_name']);
        }

        // Ensure slug exists and is unique
        if (!empty($data['title'])) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $attempt = 0;
            while ($this->designationRepository->slugExists($slug) && $attempt < 50) {
                $attempt++;
                $slug = $baseSlug . '-' . $attempt;
            }

            if ($this->designationRepository->slugExists($slug)) {
                throw new DomainException('Unable to generate a unique slug for the designation.');
            }

            $data['slug'] = $slug;
        }

        // Default status
        if (!isset($data['status'])) {
            $data['status'] = '1';
        }

        return $this->designationRepository->createDesignation($data);
    }

    public function getDesignation($id)
    {
        return $this->designationRepository->getDesignationById($id);
    }

    public function updateDesignation($id, array $data)
    {
        // Normalize title key
        if (isset($data['designation_name']) && !isset($data['title'])) {
            $data['title'] = $data['designation_name'];
            unset($data['designation_name']);
        }

        // Regenerate slug if title changed and ensure uniqueness (exclude this id)
        if (!empty($data['title'])) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $attempt = 0;
            while ($this->designationRepository->slugExists($slug, $id) && $attempt < 50) {
                $attempt++;
                $slug = $baseSlug . '-' . $attempt;
            }

            if ($this->designationRepository->slugExists($slug, $id)) {
                throw new DomainException('Unable to generate a unique slug for the designation.');
            }

            $data['slug'] = $slug;
        }

        return $this->designationRepository->updateDesignation($id, $data);
    }

    public function deleteDesignation($id)
    {
        return $this->designationRepository->deleteDesignation($id);
    }
}
