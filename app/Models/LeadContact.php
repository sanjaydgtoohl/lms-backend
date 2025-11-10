<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadContact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id', // Required for relationship with Lead model
        'full_name',
        'profile_url',
        'email',
        'mobile_number',
        'mobile_number_optional',
        'type',
        'designation_id',
        'department_id',
        'sub_source_id',
        'country_id',
        'state_id',
        'city_id',
        'zone_id',
        'postal_code',
        'status', // Added in new migration
    ];

    /**
     * Get the lead that owns the contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead()
    {
        // Define relationship using lead_id as foreign key
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}