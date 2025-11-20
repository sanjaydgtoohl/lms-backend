<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class PermissionService
{
	protected PermissionRepositoryInterface $permissionRepository;
	protected ResponseService $responseService;

	public function __construct(PermissionRepositoryInterface $permissionRepository, ResponseService $responseService)
	{
		$this->permissionRepository = $permissionRepository;
		$this->responseService = $responseService;
	}

	/**
	 * Get paginated permissions
	 */
	public function list(array $criteria = [], int $perPage = 15): LengthAwarePaginator
	{
		return $this->permissionRepository->search($criteria, $perPage);
	}

	/**
	 * Find permission by id
	 */
	public function find(int $id): ?Permission
	{
		return $this->permissionRepository->find($id);
	}

	/**
	 * Find permission by uuid
	 */
	public function findByUuid(string $uuid): ?Permission
	{
		return $this->permissionRepository->findByUuid($uuid);
	}

	/**
	 * Find permission by name
	 */
	public function findByName(string $name): ?Permission
	{
		return $this->permissionRepository->findByName($name);
	}

	/**
	 * Find permission by slug
	 */
	public function findBySlug(string $slug): ?Permission
	{
		return $this->permissionRepository->findBySlug($slug);
	}

	/**
	 * Create a new permission and optionally sync roles
	 *
	 * @param array $data Permission data (name, display_name, description, slug, url, icon_file, icon_text, is_parent, status)
	 * @param array $roles Array of role IDs to assign to the permission
	 * @return Permission
	 * @throws ValidationException
	 */
	public function create(array $data, array $roles = []): Permission
	{
		$this->validatePermissionData($data);

		// Auto-generate UUID if not provided
		if (!isset($data['uuid'])) {
			$data['uuid'] = (string) Str::uuid();
		}

		// Auto-generate slug if not provided
		if (!isset($data['slug']) && isset($data['display_name'])) {
			$baseSlug = Str::slug($data['display_name'] ?? $data['name']);
			$slug = $baseSlug;
			$counter = 1;
			
			// Ensure slug is unique
			while ($this->permissionRepository->findBySlug($slug)) {
				$slug = $baseSlug . '-' . $counter;
				$counter++;
			}
			$data['slug'] = $slug;
		} elseif (!isset($data['slug']) && isset($data['name'])) {
			$baseSlug = Str::slug($data['name']);
			$slug = $baseSlug;
			$counter = 1;
			
			// Ensure slug is unique
			while ($this->permissionRepository->findBySlug($slug)) {
				$slug = $baseSlug . '-' . $counter;
				$counter++;
			}
			$data['slug'] = $slug;
		}

		// Set default status if not provided
		if (!isset($data['status'])) {
			$data['status'] = '1';
		}

		// // Set default is_parent if not provided
		// if (!isset($data['is_parent'])) {
		// 	$data['is_parent'] = false;
		// }

		// Create the permission
		$permission = $this->permissionRepository->create($data);

		// Sync roles if provided
		if (!empty($roles)) {
			// Ensure role IDs are integers
			$roleIds = array_map('intval', $roles);
			$permission->roles()->sync($roleIds);
		}

		return $permission;
	}

	/**
	 * Update a permission
	 *
	 * @param int $id Permission ID
	 * @param array $data Permission data to update (name, display_name, description, slug, url, icon_file, icon_text, is_parent, status)
	 * @param array|null $roles Array of role IDs to sync (if null, roles are not updated)
	 * @return bool
	 * @throws ValidationException
	 */
	public function update(int $id, array $data, ?array $roles = null): bool
	{
		// Normalize is_parent - handle both null and empty string
		if (array_key_exists('is_parent', $data)) {
			$isParent = $data['is_parent'];
			
			// Convert empty string, "0", 0 to null
			if ($isParent === '' || $isParent === '0' || $isParent === 0) {
				$data['is_parent'] = null;
			} elseif ($isParent !== null) {
				// Convert to integer for non-null values
				$data['is_parent'] = intval($isParent);
			}
		}

		$this->validatePermissionData($data, $id);

		// Update the permission
		$updated = $this->permissionRepository->update($id, $data);

		// Sync roles if provided
		if ($updated && $roles !== null) {
			$permission = $this->permissionRepository->find($id);
			if ($permission) {
				$roleIds = array_map('intval', $roles);
				$permission->roles()->sync($roleIds);
			}
		}

		return $updated;
	}


	/**
	 * Delete a permission
	 */
	public function delete(int $id): bool
	{
		return $this->permissionRepository->delete($id);
	}

	/**
	 * Sync roles for a permission
	 */
	public function syncRoles(int $permissionId, array $roleIds): bool
	{
		return $this->permissionRepository->syncRoles($permissionId, $roleIds);
	}

	/**
	 * Attach a role to a permission
	 */
	public function attachRole(int $permissionId, int $roleId): bool
	{
		return $this->permissionRepository->attachRole($permissionId, $roleId);
	}

	/**
	 * Detach a role from a permission
	 */
	public function detachRole(int $permissionId, int $roleId): bool
	{
		return $this->permissionRepository->detachRole($permissionId, $roleId);
	}

	/**
	 * Get permissions by parent status
	 */
	public function getByParentStatus(bool $isParent, int $perPage = 15): LengthAwarePaginator
	{
		return $this->permissionRepository->getByParentStatus($isParent, $perPage);
	}

	/**
	 * Get active permissions
	 */
	public function getActive(int $perPage = 15): LengthAwarePaginator
	{
		return $this->permissionRepository->getActive($perPage);
	}

	/**
	 * Upload icon file for permission
	 *
	 * @param UploadedFile $file
	 * @return string|null
	 */
	public function uploadIcon(UploadedFile $file): ?string
	{
		try {
			$model = new Permission();
			// Use HandlesFileUploads trait method
			$uploadResult = $model->uploadImage($file, 'permissions/icons', [
				'disk' => 'public',
				'prefix' => 'icon_',
			]);
			
			// Return the URL to store in database
			return $uploadResult['url'] ?? $uploadResult['path'] ?? null;
		} catch (Exception $e) {
			Log::error('Error uploading permission icon', ['exception' => $e]);
			throw new Exception('Error uploading icon file: ' . $e->getMessage());
		}
	}

	/**
	 * Delete icon file
	 *
	 * @param string $filePath
	 * @return bool
	 */
	public function deleteIcon(string $filePath): bool
	{
		try {
			$model = new Permission();
			return $model->deleteFile($filePath, 'public');
		} catch (Exception $e) {
			Log::error('Error deleting permission icon', ['file_path' => $filePath, 'exception' => $e]);
			return false;
		}
	}

	/**
	 * Validate permission data
	 *
	 * @param array $data
	 * @param int|null $ignoreId
	 * @throws ValidationException
	 */
	protected function validatePermissionData(array $data, ?int $ignoreId = null): void
	{
		$rules = [
			'name' => 'required|string|max:255|unique:permissions,name' . ($ignoreId ? ",{$ignoreId}" : ''),
			'display_name' => 'nullable|string|max:255',
			'description' => 'nullable|string|max:1000',
			'slug' => 'nullable|string|max:255|unique:permissions,slug' . ($ignoreId ? ",{$ignoreId}" : ''),
			'url' => 'nullable|string|max:255',
			'icon_file' => 'nullable|string|max:255',
			'icon_text' => 'nullable|string|max:255',
			'is_parent' => [
				'nullable',
				'integer',
				'min:1',
				function ($attribute, $value, $fail) use ($ignoreId) {
					if ($value !== null) {
						// Check if the parent permission exists
						$exists = Permission::where('id', $value)->exists();
						if (!$exists) {
							$fail('The selected parent permission does not exist.');
						}
						// Prevent self-reference
						if ($ignoreId && $value == $ignoreId) {
							$fail('A permission cannot be its own parent.');
						}
					}
				}
			],
			'status' => 'nullable|in:1,2,15',
			'uuid' => 'nullable|uuid|unique:permissions,uuid' . ($ignoreId ? ",{$ignoreId}" : ''),
		];

		$validator = Validator::make($data, $rules);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}
}

