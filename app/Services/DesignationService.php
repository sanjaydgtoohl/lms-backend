<?php

namespace App\Services;

use App\Contracts\Repositories\DesignationRepositoryInterface;
use Illuminate\Support\Str;
use DomainException;
use Illuminate\Database\QueryException;
use Exception;

class DesignationService
{
    protected $designationRepository;

    public function __construct(DesignationRepositoryInterface $designationRepository)
    {
        $this->designationRepository = $designationRepository;
    }

    public function getAllDesignations()
    {
        try {
            return $this->designationRepository->getAllDesignations();
        } catch (QueryException $e) {
            throw new DomainException('Database error while fetching designations: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while fetching designations: ' . $e->getMessage());
        }
    }

    public function createNewDesignation(array $data)
    {
        try {
            if (isset($data['designation_name']) && !isset($data['title'])) {
                $data['title'] = $data['designation_name'];
                unset($data['designation_name']);
            }

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

            if (!isset($data['status'])) {
                $data['status'] = '1';
            }

            return $this->designationRepository->createDesignation($data);
        } catch (QueryException $e) {
            throw new DomainException('Database error while creating designation: ' . $e->getMessage());
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while creating designation: ' . $e->getMessage());
        }
    }

    public function getDesignation($id)
    {
        try {
            return $this->designationRepository->getDesignationById($id);
        } catch (QueryException $e) {
            throw new DomainException('Database error while fetching designation: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Designation not found or unexpected error occurred.');
        }
    }

    public function updateDesignation($id, array $data)
    {
        try {
            if (isset($data['designation_name']) && !isset($data['title'])) {
                $data['title'] = $data['designation_name'];
                unset($data['designation_name']);
            }

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
        } catch (QueryException $e) {
            throw new DomainException('Database error while updating designation: ' . $e->getMessage());
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while updating designation: ' . $e->getMessage());
        }
    }

    public function deleteDesignation($id)
    {
        try {
            return $this->designationRepository->deleteDesignation($id);
        } catch (QueryException $e) {
            throw new DomainException('Database error while deleting designation: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while deleting designation: ' . $e->getMessage());
        }
    }
}
