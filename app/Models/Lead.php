<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Support\UserAccessScope;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

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
        'lead_type_id',
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
        'pre_lead_id',
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
     * Super Admin sees all. Others see only leads where they are creator or assigned user.
     *
     * @param Builder $query
     * @param mixed $user
     * @return Builder
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->whereNull($this->getTable() . '.deleted_at');
    }

    public function scopeAccessibleToUser(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        if (UserAccessScope::hasGlobalRecordAccess($user)) {
            return $query;
        }

        UserAccessScope::applyVisibleUserFilter(
            $query,
            $user,
            ['created_by', 'current_assign_user'],
            $this->getTable()
        );

        return $query;
    }

    /**
     * Get all parent IDs (including transitive parents) for a user.
     *
     * @param int $userId
     * @return array
     */
    private function getAllParentIds(int $userId): array
    {
        $parentIds = [];
        $visited = [];
        $queue = [$userId];

        while (!empty($queue)) {
            $currentUserId = array_shift($queue);

            if (isset($visited[$currentUserId])) {
                continue;
            }
            
            $visited[$currentUserId] = true;

            // Get direct parents of current user
            $directParents = \DB::table('user_parent')
                ->where('user_id', $currentUserId)
                ->pluck('is_parent')
                ->toArray();

            foreach ($directParents as $parentId) {
                if (!isset($visited[$parentId])) {
                    $parentIds[] = $parentId;
                    $queue[] = $parentId;
                }
            }
        }

        return $parentIds;
    }
    
    private function getDirectChildsIds(int $userId): array
    {
        return UserParent::where('is_parent', $userId)->pluck('user_id')->toArray();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function leadType()
    {
        return $this->belongsTo(LeadType::class, 'lead_type_id');
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

    /**
     * Get all notifications for this lead.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}