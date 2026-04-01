<?php

namespace App\Models;

use App\Traits\HandlesFileUploads;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MissCampaign
 *
 * @package App\Models
 */
class MissCampaign extends BaseModel
{
    use HandlesFileUploads;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'miss_campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'brand_id',
        'lead_source_id',
        'lead_sub_source_id',
        'image_path',
        'assign_by',
        'assign_to',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
        'brand_id' => 'integer',
        'lead_source_id' => 'integer',
        'lead_sub_source_id' => 'integer',
        'assign_by' => 'integer',
        'assign_to' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the brand that owns the campaign.
     *
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Get the lead source associated with the campaign.
     *
     * @return BelongsTo
     */
    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    /**
     * Get the lead sub source associated with the campaign.
     *
     * @return BelongsTo
     */
    public function leadSubSource(): BelongsTo
    {
        return $this->belongsTo(LeadSubSource::class, 'lead_sub_source_id');
    }

    /**
     * Get the user who assigned the campaign.
     *
     * @return BelongsTo
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    /**
     * Get the user to whom the campaign is assigned.
     *
     * @return BelongsTo
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_to');
    }
}
