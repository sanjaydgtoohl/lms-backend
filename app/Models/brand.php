<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    // Use the SoftDeletes trait to enable soft deleting
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'contact_person_id',
        'brand_type_id',
        'industry_id',
        'country_id',
        'state_id',
        'city_id',
        'region_id',
        'subregions_id',
        'agency_id',
        'created_by',
        'website',
        'postal_code',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // Casts can be added here if needed, e.g.
        // 'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    // --- DEFINE RELATIONSHIPS ---
    // These are needed for the 'with()' in the controller's index/show methods

    public function brandType()
    {
        return $this->belongsTo(BrandType::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class); // 'Zone'
    }

    public function subregions()
    {
        return $this->belongsTo(SubRegion::class, 'subregions_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function contactPerson()
    {
        return $this->belongsTo(User::class, 'contact_person_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}