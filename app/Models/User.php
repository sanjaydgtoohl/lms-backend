<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// use Illuminate\Database\Eloquent\SoftDeletes as EloquentSoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\HasTimestamps;
use App\Traits\HasUuid;
use App\Traits\HasApiTokens;
use App\Traits\SoftDeletes;
use App\Models\LoginLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory,HasTimestamps, HasUuid, HasApiTokens, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'refresh_token',
        'phone',
        'avatar',
        'status',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        
        'password',
        'refresh_token',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        //'created_at_formatted',
        //'updated_at_formatted',
        'created_at_human',
        'updated_at_human',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $roles = $this->roles()->pluck('name')->toArray();
        return [
            'roles' => $roles,
            'status' => $this->status,
        ];
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === '1';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return \Illuminate\Support\Facades\Storage::url('avatars/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    /**
     * Scope for admin users
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        });
    }

    /**
     * Scope for verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Get user roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withPivot('user_type')
            ->withTimestamps();
    }

    /**
     * Get user permissions
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
            ->withPivot('user_type')
            ->withTimestamps();
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    /**
     * Get the login logs for the user.
     */
    public function loginLogs(): HasMany
    {
        // Sort by login_time in descending order (latest first)
        return $this->hasMany(LoginLog::class)->latest('login_time');
    }

    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        $userRoleCount = $this->roles()->whereIn('name', $roles)->count();
        return $userRoleCount === count($roles);
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        // Check direct permissions
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Check role permissions
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // Check direct permissions
        if ($this->permissions()->whereIn('name', $permissions)->exists()) {
            return true;
        }

        // Check role permissions
        return $this->roles()->whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn('name', $permissions);
        })->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissionCount = $this->permissions()->whereIn('name', $permissions)->count();
        
        if ($userPermissionCount === count($permissions)) {
            return true;
        }

        // Check role permissions
        $rolePermissionCount = $this->roles()->whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn('name', $permissions);
        })->count();

        return ($userPermissionCount + $rolePermissionCount) >= count($permissions);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([
            $role->id => [
                'user_type' => static::class,
            ]
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Give permission to user
     */
    public function givePermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([
            $permission->id => [
                'user_type' => static::class,
            ]
        ]);
    }

    /**
     * Remove permission from user
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Get all permissions for user (including role permissions)
     */
    public function getAllPermissions()
    {
        $directPermissions = $this->permissions;
        $rolePermissions = $this->roles()->with('permissions')->get()->pluck('permissions')->flatten();
        
        return $directPermissions->merge($rolePermissions)->unique('id');
    }
}