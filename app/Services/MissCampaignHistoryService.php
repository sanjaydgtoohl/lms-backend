<?php

namespace App\Services;

use App\Repositories\MissCampaignHistoryRepository;

class MissCampaignHistoryService
{
    protected $repository;

    public function __construct(MissCampaignHistoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByMissCampaignId($missCampaignId)
    {
        return $this->repository->getByMissCampaignId($missCampaignId);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getById($id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
