<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\ResponseService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * The user service instance
     *
     * @var UserService
     */
    protected $userService;

    /**
     * The response service instance
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Constructor
     *
     * @param UserService $userService
     * @param ResponseService $responseService
     */
    public function __construct(UserService $userService, ResponseService $responseService)
    {
        $this->userService = $userService;
        $this->responseService = $responseService;
    }

    /**
     * Get all users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $users = $this->userService->getAllUsers($perPage);
            
            return $this->responseService->paginated(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve users: ' . $e->getMessage());
        }
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return $this->responseService->notFound('User not found');
            }
            
            return $this->responseService->success(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Create new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->all());
            
            return $this->responseService->created(
                new UserResource($user),
                'User created successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'User creation validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $success = $this->userService->updateUser($id, $request->all());
            
            if (!$success) {
                return $this->responseService->notFound('User not found');
            }
            
            $user = $this->userService->getUserById($id);
            
            return $this->responseService->updated(
                new UserResource($user),
                'User updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'User update validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->userService->deleteUser($id);
            
            if (!$success) {
                return $this->responseService->notFound('User not found');
            }
            
            return $this->responseService->deleted('User deleted successfully');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Search users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only(['name', 'email', 'role', 'status', 'created_at']);
            $perPage = $request->get('per_page', 15);
            
            $users = $this->userService->searchUsers($criteria, $perPage);
            
            return $this->responseService->paginated(
                UserResource::collection($users),
                'Search results retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to search users: ' . $e->getMessage());
        }
    }

    /**
     * Get user statistics
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->userService->getUserStatistics();
            
            return $this->responseService->success($statistics, 'User statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve user statistics: ' . $e->getMessage());
        }
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function changePassword(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
            ]);

            $success = $this->userService->changePassword(
                $id,
                $request->current_password,
                $request->password
            );
            
            if (!$success) {
                return $this->responseService->notFound('User not found');
            }
            
            return $this->responseService->success(null, 'Password changed successfully');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Password change validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to change password: ' . $e->getMessage());
        }
    }
}
