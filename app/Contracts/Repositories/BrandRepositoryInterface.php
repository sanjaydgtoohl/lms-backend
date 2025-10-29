<?php

namespace App\Contracts\Repositories;

interface BrandRepositoryInterface
{
    /**
     * Fetch paginated list of brands (with relations as needed)
     */
    public function getAllBrands(int $perPage = 10);

    /**
     * Fetch a single brand by id
     * @param int $id
     */
    public function getBrandById($id);

    /**
     * Create a brand
     * @param array $data
     */
    public function createBrand(array $data);

    /**
     * Update a brand by id
     * @param int $id
     * @param array $data
     */
    public function updateBrand($id, array $data);

    /**
     * Soft delete a brand by id
     * @param int $id
     */
    public function deleteBrand($id);
}


