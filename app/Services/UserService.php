<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * The user repository instance
     *
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->all($perPage);
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findWithRelations($id, ['profile']);
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function createUser(array $data): User
    {
        $this->validateUserData($data);
    
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Set default values
        $data['status'] = $data['status'] ?? 'active';
        // $data['role'] = $data['role'] ?? 'user';

        return $this->userRepository->create($data);
    }

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ValidationException
     */
    public function updateUser(int $id, array $data): bool
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return false;
        }

        // Validate data for update
        $this->validateUserData($data, $id);

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * Authenticate user
     *
     * @param array $credentials
     * @return User|null
     */
    public function authenticateUser(array $credentials): ?User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        if (!$user->isActive()) {
            return null;
        }

        // Update last login time
        $this->userRepository->updateLastLogin($user->id);

        return $user;
    }

    /**
     * Search users
     *
     * @param array $criteria
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchUsers(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->search($criteria, $perPage);
    }

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getUserStatistics(): array
    {
        return $this->userRepository->getStatistics();
    }

    /**
     * Change user password
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws ValidationException
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return false;
        }

        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.']
            ]);
        }

        $this->validatePassword($newPassword);

        return $this->userRepository->update($userId, [
            'password' => Hash::make($newPassword)
        ]);
    }

    /**
     * Validate user data
     *
     * @param array $data
     * @param int|null $userId
     * @return void
     * @throws ValidationException
     */
    protected function validateUserData(array $data, ?int $userId = null): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'sometimes|required|string|min:8',
            'phone' => 'nullable|string|max:20',
            // 'role' => 'sometimes|in:admin,user,moderator',
            'status' => 'sometimes|in:active,inactive,suspended',
        ];

        // Add unique email rule if creating new user or updating email
        if (!$userId || isset($data['email'])) {
            $emailRule = 'required|email|max:255|unique:users,email';
            if ($userId) {
                $emailRule .= ',' . $userId;
            }
            $rules['email'] = $emailRule;
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate password
     *
     * @param string $password
     * @return void
     * @throws ValidationException
     */
    protected function validatePassword(string $password): void
    {
        $validator = Validator::make(['password' => $password], [
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}