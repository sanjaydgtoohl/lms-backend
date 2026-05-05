<?php

/**
 * LeadType Model
 * -----------------------------------------
 * Represents the lead_types table and handles lead type data,
 * including status management and reusable query scopes.
 *
 * @package App\Models
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * LeadType Model
 *
 * Represents the lead_types table and provides basic Eloquent
 * behaviour including soft deletes and a convenience scope.
 */
class LeadType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lead_types';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public $timestamps = true;

    /**
     * Scope an active query.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }
}
