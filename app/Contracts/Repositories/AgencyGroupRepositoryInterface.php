<?php

namespace App\Contracts\Repositories;

use App\Models\Agency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AgencyGroupRepositoryInterface
{
    public function getAllGroups(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator;

    public function getGroupById(int $id): ?Agency;

    public function createGroup(array $data): Agency;

    public function updateGroup(int $id, array $data): Agency;

    public function deleteGroup(int $id): bool;
}
