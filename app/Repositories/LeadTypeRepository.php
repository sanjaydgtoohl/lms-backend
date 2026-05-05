<?php

/**
 * Lead Type Repository
 * -----------------------------------------
 * Implements the LeadTypeRepositoryInterface to provide data access methods for lead types.
 * This repository interacts with the LeadType Eloquent model to perform CRUD operations,
 * including pagination and search functionality for lead types.
 *
 * @package App\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

namespace App\Repositories;

use App\Contracts\Repositories\LeadTypeRepositoryInterface;
use App\Models\LeadType;

class LeadTypeRepository implements LeadTypeRepositoryInterface
{
    protected LeadType $model;

    public function __construct(LeadType $leadType)
    {
        $this->model = $leadType;
    }

    public function getAllLeadTypes(int $perPage = 10, ?string $searchTerm = null)
    {
        $query = $this->model->newQuery();

        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('created_at', 'asc')->paginate($perPage);
    }

    public function listLeadTypes()
    {
        return $this->model
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    public function getLeadTypeById(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function createLeadType(array $data)
    {
        return $this->model->create($data);
    }
}
