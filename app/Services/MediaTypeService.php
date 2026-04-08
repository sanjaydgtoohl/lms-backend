<?php

/**
 * MediaType Service
 * -----------------------------------------
 * Handles business logic for media type operations, including validation and data transformation.
 *
 * @package App\Services
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Services;

use App\Contracts\Repositories\MediaTypeRepositoryInterface;
use App\Models\MediaType;
use Illuminate\Support\Str;

class MediaTypeService
{
    protected MediaTypeRepositoryInterface $repository;

    public function __construct(MediaTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(int $perPage = 15)
    {
        return $this->repository->getAllMediaTypes($perPage);
    }

    public function findById(int $id): MediaType
    {
        return $this->repository->getMediaTypeById($id);
    }

    public function create(array $data): MediaType
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->repository->createMediaType($data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->deleteMediaType($id);
    }
}
