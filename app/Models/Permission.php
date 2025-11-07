<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laratrust\Models\Permission as PermissionModel;
use Illuminate\Support\Str;

class Permission extends PermissionModel
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
     * Status constants that map to the DB enum in the migration.
     */
    public const STATUS_ACTIVE = '1';
    public const STATUS_DEACTIVATED = '2';
    public const STATUS_SOFT_DELETED = '15';
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'uuid' => 'string',
        'slug' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model and attach event listeners to generate UUID and slug.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure uuid and slug are set when creating
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                // Use Laravel's Str::uuid() to generate a UUID
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }
        });

        // Keep slug in sync with name on save
        static::saving(function ($model) {
            if (! empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Use uuid for route model binding instead of the id.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Scope a query to only active permissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}