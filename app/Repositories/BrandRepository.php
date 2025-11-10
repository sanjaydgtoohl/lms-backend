<?php

namespace App\Repositories;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BrandRepository implements BrandRepositoryInterface
{
    /**
     * Default relationships to eager load.
     *
     * @var array<string>
     */
    protected const DEFAULT_RELATIONSHIPS = [
        'agency',
        'zone',
        'brandType',
        'industry',
        'country',
        'state',
        'city',
    ];

    /**
     * @var Brand
     */
    protected Brand $model;

    /**
     * Create a new BrandRepository instance.
     *
     * @param Brand $brand
     */
    public function __construct(Brand $brand)
    {
        $this->model = $brand;
    }

    // ============================================================================
    // READ OPERATIONS
    // ============================================================================

    /**
     * Fetch paginated list of brands with relationships.
     *
     * @param int $perPage
     * @param string|null $searchTerm
     * @return LengthAwarePaginator
     */
    public function getAllBrands(int $perPage = 10, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('status', '1');

        // Apply search filter if search term is provided
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('slug', 'LIKE', "%{$searchTerm}%");
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Get a simple list of brands (ID and Name).
     *
     * @return Collection|null
     */
    public function getBrandList(): ?Collection
    {
        return $this->model
            ->select('id', 'name')        // Select only id and name
            ->where('status', '1')        // Match 'active' status from getAllBrands
            ->orderBy('name', 'asc')      // Order alphabetically by name
            ->get();
    }

    /**
     * Fetch a single brand by its primary ID.
     *
     * @param int $id
     * @return Brand|null
     */
    public function getBrandById(int $id): ?Brand
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->find($id);
    }

    /**
     * Fetch a single brand by its unique slug.
     *
     * @param string $slug
     * @return Brand|null
     */
    public function getBrandBySlug(string $slug): ?Brand
    {
        return $this->model
            ->with(self::DEFAULT_RELATIONSHIPS)
            ->where('slug', $slug)
            ->first();
    }

    // ============================================================================
    // WRITE OPERATIONS
    // ============================================================================

    /**
     * Create a new brand record.
     *
     * @param array<string, mixed> $data
     * @return Brand
     */
    public function createBrand(array $data): Brand
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing brand by ID.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateBrand(int $id, array $data): bool
    {
        $brand = $this->model->findOrFail($id);
        return $brand->update($data);
    }

    /**
     * Soft delete a brand by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBrand(int $id): bool
    {
        $brand = $this->model->findOrFail($id);
        return $brand->delete();
    }
}
