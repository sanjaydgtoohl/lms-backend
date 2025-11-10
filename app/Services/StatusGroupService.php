<?php

namespace App\Services;

use App\Models\StatusGroup;
use App\Contracts\Repositories\StatusGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class StatusGroupService
{
    protected $statusGroupRepository;

    public function __construct(StatusGroupRepositoryInterface $statusGroupRepository)
    {
        $this->statusGroupRepository = $statusGroupRepository;
    }

    public function getAllActivePaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            return $this->statusGroupRepository->paginateActive($perPage, $filters);
        } catch (QueryException $e) {
            throw new RuntimeException('Database error while fetching status groups: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error retrieving status groups: ' . $e->getMessage(), 0, $e);
        }
    }

    public function validateGroupData(array $data): array
    {
        $validator = validator($data, [
            'name' => 'required|string|max:255|unique:status_groups,name',
            'status_id' => 'required|array',
            'status_id.*' => 'integer',
            'status' => 'sometimes|in:1,2,15',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    public function createGroup(array $data): StatusGroup
    {
        try {
            // Validate the data
            $validatedData = $this->validateGroupData($data);
            
            // Business logic: Set default status if not provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = '1';
            }
            
            return $this->statusGroupRepository->create($validatedData);
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            throw new RuntimeException('Database error while creating status group: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error creating status group: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getGroupById(string $id): StatusGroup
    {
        try {
            return $this->statusGroupRepository->findById($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Status group not found with ID: ' . $id);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error retrieving status group: ' . $e->getMessage(), 0, $e);
        }
    }

    public function updateGroup(string $id, array $data): StatusGroup
    {
        try {
            // First, find the group
            $statusGroup = $this->statusGroupRepository->findById($id);
            
            // Then, update it
            return $this->statusGroupRepository->update($statusGroup, $data);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Status group not found with ID: ' . $id);
        } catch (QueryException $e) {
            throw new RuntimeException('Database error while updating status group: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error updating status group: ' . $e->getMessage(), 0, $e);
        }
    }

    public function deleteGroup(string $id): bool
    {
        try {
            $statusGroup = $this->statusGroupRepository->findById($id);
            return $this->statusGroupRepository->delete($statusGroup);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Status group not found with ID: ' . $id);
        } catch (QueryException $e) {
            throw new RuntimeException('Database error while deleting status group: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error deleting status group: ' . $e->getMessage(), 0, $e);
        }
    }

    public function searchGroups(string $query, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->statusGroupRepository->search($query, $perPage);
        } catch (QueryException $e) {
            throw new RuntimeException('Database error while searching status groups: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error searching status groups: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getStatistics(): array
    {
        try {
            // Orchestrate multiple repository calls
            $total = $this->statusGroupRepository->getTotalCount();
            $active = $this->statusGroupRepository->getActiveCount();
            $deactivated = $this->statusGroupRepository->getDeactivatedCount();
            $deleted = $this->statusGroupRepository->getTrashedCount();

            return [
                'total_all' => $total,
                'total_active' => $active,
                'total_deactivated' => $deactivated,
                'total_deleted' => $deleted,
            ];
        } catch (QueryException $e) {
            throw new RuntimeException('Database error while fetching status group statistics: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error fetching status group statistics: ' . $e->getMessage(), 0, $e);
        }
    }
}