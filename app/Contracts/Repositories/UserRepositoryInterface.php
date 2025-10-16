<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Get all users with pagination
     */
    public function all(int $perPage = 15);

    /**
     * Find user by ID
     */
    public function find(int $id): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Update user by ID
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete user by ID
     */
    public function delete(int $id): bool;

    /**
     * Search users by criteria
     */
    public function search(array $criteria, int $perPage = 15);

    /**
     * Get user with relationships
     */
    public function findWithRelations(int $id, array $relations = []): ?User;
}
