<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15)
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
        return $this->userRepository->findWithRelations($id, ['profile', 'roles', 'permissions']);
    }

    /**
     * Get user by email
     *
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
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
        $data['status'] = $data['status'] ?? '1';

        // Extract role_id or role (accept both formats)
        $roleIds = $data['role_id'] ?? $data['role'] ?? [];
        unset($data['role_id'], $data['role']);

        $user = $this->userRepository->create($data);

        // Sync roles if provided
        if (!empty($roleIds)) {
            $this->syncUserRoles($user->id, $roleIds);
        }

        // Reload user with relationships
        return $this->userRepository->findWithRelations($user->id, ['profile', 'roles', 'permissions']);
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

        // Validate data for update (includes role_id validation)
        $this->validateUserData($data, $id);

        // Extract role_id or role (accept both formats)
        $roleIds = $data['role_id'] ?? $data['role'] ?? null;

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Remove role_id and role from data before updating user record
        unset($data['role_id'], $data['role']);

        $success = $this->userRepository->update($id, $data);

        // Sync roles if provided
        if ($roleIds !== null && is_array($roleIds)) {
            $this->syncUserRoles($id, $roleIds);
        }

        return $success;
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchUsers(array $criteria, int $perPage = 15)
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
            'role_id' => 'sometimes|array',
            'role_id.*' => 'integer|exists:roles,id',
            'role' => 'sometimes|array',
            'role.*' => 'integer|exists:roles,id',
            'status' => 'sometimes|in:1,2,3',
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
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Sync user roles
     *
     * @param int $userId
     * @param array $roleIds
     * @return void
     */
    public function syncUserRoles(int $userId, array $roleIds): void
    {
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return;
        }

        // Get the user type class
        $userType = User::class;

        // First, delete all existing role entries for this user
        DB::table('role_user')
            ->where('user_id', $userId)
            ->delete();

        // Insert new role entries
        $insertData = [];
        foreach ($roleIds as $roleId) {
            $insertData[] = [
                'role_id' => $roleId,
                'user_id' => $userId,
                'user_type' => $userType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            DB::table('role_user')->insert($insertData);
        }
    }
}