<?php

/**
 * MediaType Repository Interface
 * -----------------------------------------
 * Defines the contract for MediaType repository operations, including CRUD and pagination methods.
 *
 * @package App\Contracts\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Contracts\Repositories;

use App\Models\MediaType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MediaTypeRepositoryInterface
{
    /**
     * Get paginated list of media types.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllMediaTypes(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a media type by ID.
     *
     * @param int $id
     * @return MediaType
     */
    public function getMediaTypeById(int $id): MediaType;

    /**
     * Create a new media type.
     *
     * @param array $data
     * @return MediaType
     */
    public function createMediaType(array $data): MediaType;

    /**
     * Soft delete a media type by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteMediaType(int $id): bool;

    /**
     * Update an existing media type.
     *
     * @param int $id
     * @param array $data
     * @return MediaType
     */
    public function updateMediaType(int $id, array $data): MediaType;

    /**
     * Get a list of media types for dropdown selection.
     *
     * @return mixed
     */
    public function listMediaTypes();
}
