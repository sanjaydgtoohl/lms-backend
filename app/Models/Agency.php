<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agency';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'agency_type',
        'brand_id',
        'is_parent'
    ];

    /**
     * Get the group this agency belongs to.
     */
    public function agencyGroup()
    {
        return $this->belongsTo(AgencyGroup::class, 'agency_group_id');
    }

    /**
     * Get the type of this agency.
     */
    public function agencyType()
    {
        return $this->belongsTo(AgencyType::class, 'agency_type_id');
    }

    /**
     * Get the brand (client) this agency is for.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id'); // Assumes App\Models\Brand
    }
}