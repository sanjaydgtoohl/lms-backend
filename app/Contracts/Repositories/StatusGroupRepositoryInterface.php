<?php

namespace App\Contracts\Repositories;

use App\Models\StatusGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StatusGroupRepositoryInterface
{
    /**
     * Get paginated active status groups.
     */
    public function paginateActive(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Create a new status group.
     * @throws \Illuminate\Database\QueryException
     */
    public function create(array $data): StatusGroup;

    /**
     * Find a status group by its ID.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(string $id): StatusGroup;

    /**
     * Update a given status group.
     * @throws \Illuminate\Database\QueryException
     */
    public function update(StatusGroup $statusGroup, array $data): StatusGroup;

    /**
     * Soft delete a given status group.
     * @throws \Exception
     */
    public function delete(StatusGroup $statusGroup): bool;

    /**
     * Search for active status groups by name.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get various counts for statistics.
     */
    public function getTotalCount(): int;
    public function getActiveCount(): int;
    public function getDeactivatedCount(): int;
    public function getTrashedCount(): int;
}