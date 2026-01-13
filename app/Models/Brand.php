<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'brand_type_id',
        'industry_id',
        'country_id',
        'state_id',
        'city_id',
        'zone_id',
        'created_by',
        'website',
        'postal_code',
        'status',
        'contact_person_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the brand type that owns the brand.
     *
     * @return BelongsTo
     */
    public function brandType(): BelongsTo
    {
        return $this->belongsTo(BrandType::class);
    }

    /**
     * Get the industry that owns the brand.
     *
     * @return BelongsTo
     */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    /**
     * Get the country that owns the brand.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state that owns the brand.
     *
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city that owns the brand.
     *
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the zone that owns the brand.
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the agency that owns the brand.
     *
     * @return BelongsTo
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the user who created the brand.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the agencies that own this brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'brand_agency_relationships', 'brand_id', 'agency_id')
                    ->withTimestamps();
    }

    /**
     * Get all contact persons (leads) for this brand.
     *
     * @return HasMany
     */
    public function contactPersons(): HasMany
    {
        return $this->hasMany(Lead::class, 'brand_id');
    }

    /**
     * Get the count of contact persons for this brand.
     *
     * @return int
     */
    public function getContactPersonCount(): int
    {
        return $this->contactPersons()->count();
    }
}