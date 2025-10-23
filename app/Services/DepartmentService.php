<?php

namespace App\Services;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use Illuminate\Support\Str;
use DomainException;
use Illuminate\Database\QueryException;
use Exception;

class DepartmentService
{
    protected $departmentRepository;

    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function getAllDepartments()
    {
        try {
            return $this->departmentRepository->getAllDepartments();
        } catch (QueryException $e) {
            throw new DomainException('Database error while fetching departments: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while fetching departments: ' . $e->getMessage());
        }
    }

    public function createNewDepartment(array $data)
    {
        try {
            if (empty($data['name'])) {
                throw new DomainException('Department name is required.');
            }

            $data['slug'] = $this->createUniqueSlug($data['name']);
            $data['status'] = $data['status'] ?? '1';

            return $this->departmentRepository->createDepartment($data);
        } catch (QueryException $e) {
            throw new DomainException('Database error while creating department: ' . $e->getMessage());
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while creating department: ' . $e->getMessage());
        }
    }

    public function getDepartment($id)
    {
        try {
            $department = $this->departmentRepository->getDepartmentById($id);
            if (!$department) {
                throw new DomainException('Department not found.');
            }
            return $department;
        } catch (QueryException $e) {
            throw new DomainException('Database error while fetching department: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while fetching department: ' . $e->getMessage());
        }
    }

    public function updateDepartment($id, array $data)
    {
        try {
            $department = $this->departmentRepository->getDepartmentById($id);
            if (!$department) {
                throw new DomainException('Department not found.');
            }

            if (isset($data['name']) && $department->name !== $data['name']) {
                $data['slug'] = $this->createUniqueSlug($data['name'], $id);
            }

            return $this->departmentRepository->updateDepartment($id, $data);
        } catch (QueryException $e) {
            throw new DomainException('Database error while updating department: ' . $e->getMessage());
        } catch (DomainException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while updating department: ' . $e->getMessage());
        }
    }

    public function deleteDepartment($id)
    {
        try {
            $department = $this->departmentRepository->getDepartmentById($id);
            if (!$department) {
                throw new DomainException('Department not found.');
            }

            return $this->departmentRepository->deleteDepartment($id);
        } catch (QueryException $e) {
            throw new DomainException('Database error while deleting department: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while deleting department: ' . $e->getMessage());
        }
    }

    private function createUniqueSlug(string $name, $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        try {
            $existing = $this->departmentRepository->findBySlug($slug);

            while ($existing && $existing->id != $excludeId) {
                $slug = $originalSlug . '-' . $count++;
                $existing = $this->departmentRepository->findBySlug($slug);
            }

            return $slug;
        } catch (QueryException $e) {
            throw new DomainException('Database error while generating slug: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new DomainException('Unexpected error while generating slug: ' . $e->getMessage());
        }
    }
}
