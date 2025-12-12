<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BrandAgencyRelationship;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The "booting" method of the model.
     * Register model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($agency) {
            // Delete all brand agency relationships when agency is deleted
            BrandAgencyRelationship::where('agency_id', $agency->id)->delete();
        });
    }

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
        'is_parent',
        'contact_person_id'
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

    /**
     * Get all contact persons (leads) for this agency.
     *
     * @return HasMany
     */
    public function contactPersons(): HasMany
    {
        return $this->hasMany(Lead::class, 'agency_id');
    }

    /**
     * Get the count of contact persons for this agency.
     *
     * @return int
     */
    public function getContactPersonCount(): int
    {
        return $this->contactPersons()->count();
    }
}