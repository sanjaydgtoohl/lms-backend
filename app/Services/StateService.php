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

    public function getPaginatedStates()
    {
        return $this->stateRepo->getPaginated(10);
    }

    public function getStateById(int $id)
    {
        return $this->stateRepo->findById($id);
    }

    public function createState(array $data)
    {
        // Koi business logic nahi, seedha create
        return $this->stateRepo->create($data);
    }

    public function updateState(int $id, array $data)
    {
        // Koi business logic nahi, seedha update
        return $this->stateRepo->update($id, $data);
    }

    public function deleteState(int $id)
    {
        return $this->stateRepo->delete($id);
    }
}
