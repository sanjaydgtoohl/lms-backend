<?php

namespace App\Contracts\Repositories;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection; // <-- Import Collection


interface BrandRepositoryInterface
{
    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Fetch paginated list of brands with relationships.
     *
     * @param int $perPage The number of items per page.
     * @param string|null $searchTerm Optional search term to filter brands.
     * @return LengthAwarePaginator
     */
    public function getAllBrands(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator;

    /**
     * Fetch a single brand by its primary ID.
     *
     * @param int $id The brand ID.
     * @return Brand|null
     */
    public function getBrandById(int $id): ?Brand;

    /**
     * Fetch a single brand by its unique slug.
     *
     * @param string $slug The unique brand slug.
     * @return Brand|null
     */
    public function getBrandBySlug(string $slug): ?Brand;

    /**
     * Get a simple list of brands (ID and Name).
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getBrandList(): ?Collection;

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new brand record.
     *
     * @param array<string, mixed> $data
     * @return Brand
     */
    public function createBrand(array $data): Brand;

    /**
     * Update an existing brand by ID.
     *
     * @param int $id The brand ID.
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateBrand(int $id, array $data): bool;

    /**
     * Soft delete a brand by ID.
     *
     * @param int $id The brand ID.
     * @return bool
     */
    public function deleteBrand(int $id): bool;
}
