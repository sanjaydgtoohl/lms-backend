<?php

namespace App\Services;

use App\Contracts\Repositories\StateRepositoryInterface;

class StateService
{
    protected $stateRepo;

    public function __construct(StateRepositoryInterface $stateRepo)
    {
        $this->stateRepo = $stateRepo;
    }

    public function getAllStates()
    {
        return $this->stateRepo->getAll();
    }

    public function getStatesByCountry(int $countryId)
    {
        return $this->stateRepo->getByCountry($countryId);
    }

    public function getPaginatedStates(int $perPage = 15, ?string $search = null)
    {
        return $this->stateRepo->getPaginated($perPage, $search);
    }

    public function getStateById(int $id)
    {
        return $this->stateRepo->findById($id);
    }

    public function createState(array $data)
    {
        // No business logic needed, direct create operation
        return $this->stateRepo->create($data);
    }

    public function updateState(int $id, array $data)
    {
        // No business logic needed, direct update operation
        return $this->stateRepo->update($id, $data);
    }

    public function deleteState(int $id)
    {
        return $this->stateRepo->delete($id);
    }
}
