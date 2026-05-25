<?php

namespace App\Services;

use App\Contracts\Repositories\AgencyGroupRepositoryInterface;
use App\Models\Agency;
use DomainException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class AgencyGroupService
{
    protected AgencyGroupRepositoryInterface $repository;

    public function __construct(AgencyGroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllGroups(int $perPage = 15, ?string $searchTerm = null): LengthAwarePaginator
    {
        try {
            return $this->repository->getAllGroups($perPage, $searchTerm);
        } catch (QueryException $e) {
            Log::error('Database error fetching agency groups', ['exception' => $e]);
            throw new DomainException('Database error while fetching agency groups.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching agency groups', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching agency groups.');
        }
    }

    public function getGroup(int $id): ?Agency
    {
        try {
            return $this->repository->getGroupById($id);
        } catch (QueryException $e) {
            Log::error('Database error fetching agency group', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while fetching agency group.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching agency group', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching agency group.');
        }
    }

    public function createGroup(array $data): Agency
    {
        try {
            if (empty($data['name'])) {
                throw new DomainException('Agency group name is required.');
            }

            $data['status'] = $data['status'] ?? '1';

            return $this->repository->createGroup($data);
        } catch (DomainException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database error creating agency group', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Database error while creating agency group.');
        } catch (Exception $e) {
            Log::error('Unexpected error creating agency group', ['data' => $data, 'exception' => $e]);
            throw new DomainException('Unexpected error while creating agency group.');
        }
    }

    public function updateGroup(int $id, array $data): Agency
    {
        try {
            return $this->repository->updateGroup($id, $data);
        } catch (QueryException $e) {
            Log::error('Database error updating agency group', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while updating agency group.');
        } catch (Exception $e) {
            Log::error('Unexpected error updating agency group', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating agency group.');
        }
    }

    public function deleteGroup(int $id): bool
    {
        try {
            return $this->repository->deleteGroup($id);
        } catch (QueryException $e) {
            Log::error('Database error deleting agency group', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while deleting agency group.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting agency group', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while deleting agency group.');
        }
    }
}
