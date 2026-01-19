<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
        'uuid',
        'brand_id',
        'agency_id',
        'current_assign_user',
        'created_by',
        'priority_id',
        'call_status',
        'lead_status',
        'call_attempt',
        'name',
        'slug',
        'profile_url',
        'email',
        'type',
        'designation_id',
        'department_id',
        'sub_source_id',
        'country_id',
        'state_id',
        'city_id',
        'zone_id',
        'statuses',
        'postal_code',
        'comment',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Scope to filter leads accessible to the given user.
     * Super Admin (role_id = 8) sees all. Others see only leads where they are creator or assigned user.
     *
     * @param Builder $query
     * @param mixed $user
     * @return Builder
     */
    public function scopeAccessibleToUser(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();

        // If no user is authenticated, return empty query
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // Super Admin (role_id = 8) can view all leads
        if ($user->roles()->where('id', 8)->exists()) {
            return $query;
        }

        // Others can see leads where they are the creator (assigned_by) or assigned user (assigned_to)
        return $query->where(function (Builder $q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('current_assign_user', $user->id);
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'current_assign_user');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function subSource()
    {
        return $this->belongsTo(LeadSubSource::class, 'sub_source_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function statusRelation()
    {
        return $this->belongsTo(Status::class, 'statuses');
    }

    /**
     * Get the call status associated with this lead.
     */
    public function callStatusRelation()
    {
        return $this->belongsTo(CallStatus::class, 'call_status');
    }

    /**
     * Get the lead status associated with this lead.
     */
    public function leadStatusRelation()
    {
        return $this->belongsTo(Status::class, 'lead_status');
    }

    /**
     * Get the mobile numbers associated with this lead.
     */
    public function mobileNumbers()
    {
        return $this->hasMany(LeadMobileNumber::class, 'lead_id');
    }
}