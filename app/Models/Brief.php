<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
