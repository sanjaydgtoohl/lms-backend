<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Get users that have this permission
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user', 'permission_id', 'user_id')
            ->withPivot('user_type')
            ->withTimestamps();
    }

}
