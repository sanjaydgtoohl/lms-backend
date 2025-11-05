<?php

namespace App\Models;

use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        // Note: Laratrust setup migration didn't have a 'group_name'
        // Your controller uses it, so you might need another migration
        // to add a 'group_name' column to your 'permissions' table.
    ];
}