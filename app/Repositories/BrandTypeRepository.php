<?php

namespace App\Repositories;

use App\Contracts\Repositories\BrandTypeRepositoryInterface;
use App\Models\BrandType;
use Illuminate\Support\Collection;

class BrandTypeRepository implements BrandTypeRepositoryInterface
{
    protected $model;

    /**
     * Inject the Eloquent Model.
     *
     * @param BrandType $model
     */
    public function __construct(BrandType $model)
    {
        $this->model = $model;
    }

    /**
     * Get all active brand types.
     */
    public function getAllActive(): Collection
    {
        return $this->model
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * Get a brand type by its ID.
     */
    public function findById(int $id): BrandType
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new brand type.
     */
    public function create(array $data): BrandType
    {
        return $this->model->create($data);
    }

    /**
     * Update a brand type.
     */
    public function update(int $id, array $data): BrandType
    {
        $brandType = $this->findById($id);
        $brandType->update($data);
        return $brandType;
    }

    /**
     * Soft delete a brand type.
     */
    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }

    /**
     * Get the count of associated brands.
     */
    public function getBrandsCount(int $id): int
    {
        // We find the *model* first to ensure it exists
        // before checking the relationship.
        return $this->findById($id)->brands()->count();
    }
}
