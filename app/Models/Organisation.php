<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organisation Model
 * -----------------------------------------
 * Represents the organisations table and manages data interactions
 * using Laravel's Eloquent ORM. Handles mass assignment for
 * name, slug, and status attributes.
 *
 * @package App\Models
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

class Organisation extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'status'];
}
