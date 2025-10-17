<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\Repositories\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Return the model class associated with this repository
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Get all users with pagination
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        $modelClass = $this->modelClass;
        return $modelClass::paginate($perPage);
    }

    /**
     * Find a user by ID
     */
    public function find(int $id): ?User
    {
        return parent::find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return parent::create($data);
    }

    /**
     * Update user by ID
     */
    public function update(int $id, array $data): bool
    {
        return parent::update($id, $data);
    }

    /**
     * Delete user by ID
     */
    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    /**
     * Get user with relationships
     */
    public function findWithRelations(int $id, array $relations = []): ?User
    {
        return parent::findWithRelations($id, $relations);
    }

    /**
     * Search users by criteria with pagination
     */
    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $modelClass = $this->modelClass;
        $query = $modelClass::query();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get users by conditions
     */
    public function findBy(array $conditions): Collection
    {
        $modelClass = $this->modelClass;
        return $modelClass::where($conditions)->get();
    }

    /**
     * Get first user by conditions
     */
    public function findFirstBy(array $conditions): ?User
    {
        $modelClass = $this->modelClass;
        return $modelClass::where($conditions)->first();
    }

    /**
     * Count users by conditions
     */
    public function countBy(array $conditions): int
    {
        $modelClass = $this->modelClass;
        return $modelClass::where($conditions)->count();
    }

    /**
     * Update the last login timestamp of a user
     */
    public function updateLastLogin(int $userId): ?User
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }

        $user->last_login_at = Carbon::now();
        $user->save();

        return $user;
    }
}