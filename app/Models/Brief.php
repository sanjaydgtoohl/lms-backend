<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Brief extends Model
{
    use HasFactory, SoftDeletes;

    // Campaign Mode Constants
    const MODE_PROGRAMMATIC = 'programmatic';
    const MODE_NON_PROGRAMMATIC = 'non_programmatic';

    // Campaign Type Constants (Media Types)
    const TYPE_PROGRAMMATIC_DOOH = 'dooh';
    const TYPE_PROGRAMMATIC_CTV = 'ctv';
    const TYPE_NON_PROGRAMMATIC_DOOH = 'dooh';
    const TYPE_NON_PROGRAMMATIC_OOH = 'ooh';

    // Fields to track for history
    const TRACKED_FIELDS = ['assign_user_id', 'brief_status_id', 'submission_date'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'briefs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'product_name',
        'contact_person_id',
        'brand_id',
        'agency_id',
        'mode_of_campaign',
        'media_type',
        'budget',
        'assign_user_id',
        'created_by',
        'brief_status_id',
        'priority_id',
        'comment',
        'submission_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submission_date' => 'datetime',
        'budget' => 'decimal:2',
    ];

    /**
     * The attributes that should be appended to JSON.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'latest_planner_id',
    ];

    /**
     * The "booted" method of the model.
     * Register model event listeners.
     */
    protected static function booted()
    {
        static::updated(function ($brief) {
            $brief->saveHistoryIfFieldsChanged();
        });
    }

    /**
     * Scope to filter briefs accessible to the given user.
     * Super Admin (role_id = 8) sees all. Others see only briefs where they are creator or assigned user.
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

        // Super Admin (role_id = 8) can view all briefs
        if ($user->roles()->where('id', 8)->exists()) {
            return $query;
        }

        // Others can see briefs where they are the creator (created_by) or assigned user (assign_user_id)
        return $query->where(function (Builder $q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('assign_user_id', $user->id);
        });
    }

    // ===================================================================
    // RELATIONSHIPS
    // ===================================================================

    /**
     * Get the brief assign histories for this brief.
     */
    public function assignHistories()
    {
        return $this->hasMany(BriefAssignHistory::class, 'brief_id');
    }

    /**
     * Get the contact person (lead) associated with this brief.
     */
    public function contactPerson()
    {
        return $this->belongsTo(Lead::class, 'contact_person_id');
    }

    /**
     * Get the brand associated with this brief.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Get the agency associated with this brief.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    /**
     * Get the user assigned to this brief.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assign_user_id');
    }

    /**
     * Get the user who created this brief.
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the brief status associated with this brief.
     */
    public function briefStatus()
    {
        return $this->belongsTo(BriefStatus::class, 'brief_status_id');
    }

    /**
     * Get the priority associated with this brief.
     */
    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    /**
     * Get the latest planner id from planner history.
     *
     * @return int|null
     */
    public function getLatestPlannerIdAttribute(): ?int
    {
        return PlannerHistory::where('brief_id', $this->id)
            ->latest()
            ->value('planner_id');
    }

    // ===================================================================
    // HISTORY TRACKING METHODS
    // ===================================================================

    /**
     * Check if any tracked fields have changed and save history if needed.
     */
    private function saveHistoryIfFieldsChanged(): void
    {
        $changeData = $this->getTrackedFieldChanges();
        
        if (empty($changeData)) {
            return;
        }

        try {
            $this->createAssignHistory($changeData);
        } catch (\Exception $e) {
            Log::error('Failed to create BriefAssignHistory', [
                'brief_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the changes for tracked fields.
     */
    private function getTrackedFieldChanges(): array
    {
        $changeData = [];

        foreach (self::TRACKED_FIELDS as $field) {
            if ($this->isDirty($field)) {
                $changeData[$field] = [
                    'old' => $this->getOriginal($field),
                    'new' => $this->getAttribute($field),
                ];
            }
        }

        return $changeData;
    }

    /**
     * Create a brief assign history record.
     */
    private function createAssignHistory(array $changeData): void
    {
        BriefAssignHistory::create([
            'uuid' => Str::uuid(),
            'brief_id' => $this->id,
            'assign_by_id' => $this->getCurrentUserId(),
            'assign_to_id' => $changeData['assign_user_id']['new'] ?? $this->assign_user_id,
            'brief_status_id' => $changeData['brief_status_id']['new'] ?? $this->brief_status_id,
            'brief_status_time' => now(),
            'submission_date' => $changeData['submission_date']['new'] ?? $this->submission_date,
            'comment' => $this->comment,
            'status' => '2',
        ]);
    }

    /**
     * Get the current authenticated user ID.
     */
    private function getCurrentUserId(): ?int
    {
        return Auth::id() ?? $this->created_by;
    }
    // ===================================================================
    // STATIC HELPER METHODS
    // ===================================================================

    /**
     * Get valid campaign types for a given campaign mode.
     *
     * @param string $mode
     * @return array
     */
    public static function getCampaignTypesByMode(string $mode): array
    {
        return match ($mode) {
            self::MODE_PROGRAMMATIC => [self::TYPE_PROGRAMMATIC_DOOH, self::TYPE_PROGRAMMATIC_CTV],
            self::MODE_NON_PROGRAMMATIC => [self::TYPE_NON_PROGRAMMATIC_DOOH, self::TYPE_NON_PROGRAMMATIC_OOH],
            default => [],
        };
    }

    /**
     * Get all available campaign modes.
     *
     * @return array
     */
    public static function getCampaignModes(): array
    {
        return [
            self::MODE_PROGRAMMATIC,
            self::MODE_NON_PROGRAMMATIC,
        ];
    }

    /**
     * Get campaign mode display labels.
     *
     * @return array
     */
    public static function getCampaignModeLabels(): array
    {
        return [
            self::MODE_PROGRAMMATIC => 'Programmatic',
            self::MODE_NON_PROGRAMMATIC => 'Non-Programmatic',
        ];
    }

    /**
     * Get campaign type display labels.
     *
     * @return array
     */
    public static function getCampaignTypeLabels(): array
    {
        return [
            self::TYPE_PROGRAMMATIC_DOOH => 'DOOH',
            self::TYPE_PROGRAMMATIC_CTV => 'CTV',
            self::TYPE_NON_PROGRAMMATIC_DOOH => 'DOOH',
            self::TYPE_NON_PROGRAMMATIC_OOH => 'OOH',
        ];
    }
}
