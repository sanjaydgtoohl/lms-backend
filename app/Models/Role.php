<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laratrust\Models\Role as RoleModel;
use Illuminate\Support\Str;

class Role extends RoleModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'slug',    // <-- Added
        'uuid',   // <-- Added
        'status', // <-- Added
    ];

    /**
     * The "booted" method of the model.
     *
     * This method is automatically called by Eloquent.
     */
    protected static function boot()
    {
        parent::boot();

        // This automatically generates a UUID and a Slug
        // every time a new role is being created.
        static::creating(function ($role) {
            
            // Generate UUID if it wasn't set
            if (empty($role->uuid)) {
                $role->uuid = (string) Str::uuid();
            }

            // Generate Slug from display_name or name if it wasn't set
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->display_name ?? $role->name);
            }
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        // This tells Laravel to use the 'slug' column for route model binding
        // Example: /roles/admin-role instead of /roles/1
        return 'slug';
    }
}