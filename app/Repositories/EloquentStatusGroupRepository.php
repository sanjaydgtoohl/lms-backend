<?php

namespace App\Repositories;

use App\Models\StatusGroup;
use App\Contracts\Repositories\StatusGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class EloquentStatusGroupRepository implements StatusGroupRepositoryInterface
{
    public function paginateActive(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            $query = StatusGroup::where('status', '1');
            
            // Apply filters
            if (!empty($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }
            
            if (isset($filters['sort_by']) && isset($filters['sort_direction'])) {
                $query->orderBy($filters['sort_by'], $filters['sort_direction']);
            } else {
                $query->latest();
            }
            
            return $query->paginate($perPage);
        } catch (Exception $e) {
            throw new Exception('Error fetching status groups: ' . $e->getMessage());
        }
    }

    public function create(array $data): StatusGroup
    {
        try {
            return StatusGroup::create($data);
        } catch (QueryException $e) {
            throw new Exception('Error creating status group: ' . $e->getMessage(), 0, $e);
        }
    }

    public function findById(string $id): StatusGroup
    {
        try {
            return StatusGroup::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Status group not found with ID: ' . $id);
        }
    }

    public function update(StatusGroup $statusGroup, array $data): StatusGroup
    {
        try {
            $statusGroup->update($data);
            return $statusGroup->fresh();
        } catch (QueryException $e) {
            throw new Exception('Error updating status group: ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(StatusGroup $statusGroup): bool
    {
        try {
            return $statusGroup->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting status group: ' . $e->getMessage());
        }
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return StatusGroup::where('name', 'like', "%{$query}%")
                            ->where('status', '1')
                            ->paginate($perPage);
    }

    public function getTotalCount(): int
    {
        // count() excludes soft-deleted models by default
        return StatusGroup::count();
    }

    public function getActiveCount(): int
    {
        return StatusGroup::where('status', '1')->count();
    }

    public function getDeactivatedCount(): int
    {
        return StatusGroup::where('status', '2')->count();
    }

    public function getTrashedCount(): int
    {
        return StatusGroup::onlyTrashed()->count();
    }
}