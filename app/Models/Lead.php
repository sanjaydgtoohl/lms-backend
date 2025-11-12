<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'leads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand_id',
        'agency_id',
        'user_id', // Foreign key defined in migration
        'status',
        'comment',
    ];

    /**
     * Get all contacts associated with the lead.
     * A lead can have multiple contacts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        // Relationship defined using lead_id as foreign key in LeadContact model
        return $this->hasMany(LeadContact::class, 'lead_id');
    }

    // --- Optional: Additional relationships ---

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function brand()
    {
        // Define relationship with Brand model
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function agency()
    {
        // Define relationship with Agency model
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}