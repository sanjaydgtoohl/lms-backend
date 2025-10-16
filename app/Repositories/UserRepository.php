<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Constructor
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['profile'])->paginate($perPage);
    }

    /**
     * Search users by criteria
     *
     * @param array $criteria
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if (in_array($field, ['name', 'email'])) {
                $query->where($field, 'like', "%{$value}%");
            } elseif ($field === 'role') {
                $query->where('role', $value);
            } elseif ($field === 'status') {
                $query->where('status', $value);
            } elseif ($field === 'created_at') {
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween('created_at', $value);
                }
            }
        }

        return $query->with(['profile'])->paginate($perPage);
    }

    /**
     * Get active users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->active()->with(['profile'])->paginate($perPage);
    }

    /**
     * Get admin users
     *
     * @return Collection
     */
    public function getAdminUsers(): Collection
    {
        return $this->model->admins()->with(['profile'])->get();
    }

    /**
     * Get verified users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getVerifiedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->verified()->with(['profile'])->paginate($perPage);
    }

    /**
     * Update last login time
     *
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->model->where('id', $userId)->update([
            'last_login_at' => now()
        ]);
    }

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->where('status', 'inactive')->count(),
            'admins' => $this->model->admins()->count(),
            'verified' => $this->model->verified()->count(),
            'unverified' => $this->model->whereNull('email_verified_at')->count(),
        ];
    }
}
