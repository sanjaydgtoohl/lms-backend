<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;
use App\Models\BrandType;

interface BrandTypeRepositoryInterface 
{
    /**
     * Get all active brand types.
     *
     * @return Collection
     */
    public function getAllActive(): Collection;

    /**
     * Get a brand type by its ID.
     *
     * @param int $id
     * @return BrandType
     */
    public function findById(int $id): BrandType;

    /**
     * Create a new brand type.
     *
     * @param array $data
     * @return BrandType
     */
    public function create(array $data): BrandType;

    /**
     * Update a brand type.
     *
     * @param int $id
     * @param array $data
     * @return BrandType
     */
    public function update(int $id, array $data): BrandType;

    /**
     * Soft delete a brand type.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get the count of brands associated with a brand type.
     *
     * @param int $id
     * @return int
     */
    public function getBrandsCount(int $id): int;
}
