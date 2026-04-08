<?php

/**
 * MediaType Repository
 * -----------------------------------------
 * Implements the MediaType repository interface, providing data access layer for media types.
 *
 * @package App\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Repositories;

use App\Contracts\Repositories\MediaTypeRepositoryInterface;
use App\Models\MediaType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MediaTypeRepository implements MediaTypeRepositoryInterface
{
    protected MediaType $model;

    public function __construct(MediaType $model)
    {
        $this->model = $model;
    }

    public function getAllMediaTypes(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getMediaTypeById(int $id): MediaType
    {
        return $this->model->findOrFail($id);
    }

    public function createMediaType(array $data): MediaType
    {
        return $this->model->create($data);
    }

    public function deleteMediaType(int $id): bool
    {
        return $this->getMediaTypeById($id)->delete();
    }

    public function updateMediaType(int $id, array $data): MediaType
    {
        $mediaType = $this->getMediaTypeById($id);
        $mediaType->update($data);
        return $mediaType;
    }

    public function listMediaTypes()
    {
        return $this->model
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
    }
}
