<?php

namespace App\Services;

use App\Contracts\Repositories\AgencyTypeRepositoryInterface;
use Illuminate\Support\Str;

class AgencyTypeService
{
    protected $repo;

    public function __construct(AgencyTypeRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getTypes()
    {
        return $this->repo->allActive();
    }

    public function getTypeById(int $id)
    {
        return $this->repo->findById($id);
    }

    public function createType(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        $data['status'] = '1';
        return $this->repo->create($data);
    }

    public function updateType(int $id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->repo->update($id, $data);
    }

    public function deleteType(int $id)
    {
        return $this->repo->delete($id);
    }
}