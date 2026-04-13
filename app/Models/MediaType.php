<?php

/**
 * MediaType Model
 * -----------------------------------------
 * Represents a media type entity in the system, handling OOH, DOOH, and CTV types with soft delete support.
 *
 * @package App\Models
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaType extends Model
{
    use SoftDeletes;

    protected $table = 'media_types';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
    ];
}
