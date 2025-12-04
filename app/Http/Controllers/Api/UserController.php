<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\ResponseService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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
            $perPage = (int) $request->get('per_page', 15);
            $search = $request->input('search', null);
            
            if ($search) {
                $criteria = [
                    'search' => $search,
                ];
                $users = $this->userService->searchUsers($criteria, $perPage);
            } else {
                $users = $this->userService->getAllUsers($perPage);
            }
            
            // Apply resource collection to paginated results
            $resource = UserResource::collection($users);
            
            return $this->responseService->paginated(
                $resource,
                'Users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve users: ' . $e->getMessage());
        }
    }

    /**
     * Get list of users with only id and name (e.g., /api/v1/users/list)
     */
    public function list(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers(perPage: 10000);
            $data = $users->items() ? collect($users->items())->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }) : collect([]);
            return $this->responseService->success($data, 'Users list retrieved');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve users list: ' . $e->getMessage());
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
            // Validate required fields first
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string|max:20',
                'role_id' => 'required|array',
                'role_id.*' => 'integer|exists:roles,id',
                'status' => 'sometimes|in:1,2,3',
            ];

            $validated = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
            
            if ($validated->fails()) {
                return $this->responseService->validationError(
                    $validated->errors()->toArray(),
                    'Validation failed'
                );
            }

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

    /**
     * Get current authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user ?? auth()->user();
            
            if (!$user) {
                return $this->responseService->unauthorized('User not authenticated');
            }
            
            $user = $this->userService->getUserById($user->id);
            
            return $this->responseService->success(
                new UserResource($user),
                'User profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve user profile: ' . $e->getMessage());
        }
    }

    /**
     * Update current authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user ?? auth()->user();
            
            if (!$user) {
                return $this->responseService->unauthorized('User not authenticated');
            }

            // Remove password from update data if present (use changePassword endpoint instead)
            $data = $request->except(['password', 'password_confirmation']);
            
            $success = $this->userService->updateUser($user->id, $data);
            
            if (!$success) {
                return $this->responseService->notFound('User not found');
            }
            
            $updatedUser = $this->userService->getUserById($user->id);
            
            return $this->responseService->updated(
                new UserResource($updatedUser),
                'Profile updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Profile update validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Get login history for current authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoginHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user ?? auth()->user();
            
            if (!$user) {
                return $this->responseService->unauthorized('User not authenticated');
            }

            $loginLogs = $user->loginLogs()->paginate($request->get('per_page', 15));
            
            return $this->responseService->paginated(
                $loginLogs,
                'Login history retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve login history: ' . $e->getMessage());
        }
    }
}