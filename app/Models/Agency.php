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
     * Get the parent agency.
     */
    public function parentAgency()
    {
        return $this->belongsTo(Agency::class, 'is_parent');
    }

    /**
     * Get the type of this agency.
     */
    public function agencyType()
    {
        return $this->belongsTo(AgencyType::class, 'agency_type');
    }

    /**
     * Get the brand (client) this agency is for.
     */
    public function brand()
    {
        return $this->belongsToMany(Brand::class, 'brand_agency_relationships', 'agency_id', 'brand_id');
    }
}