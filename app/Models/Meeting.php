<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use SoftDeletes;

    protected $table = 'meetings';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'lead_id',
        'attendees_id',
        'type',
        'location',
        'agenda',
        'link',
        'meeting_date',
        'meeting_time',
        'status',
        'google_event',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'attendees_id' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship with Lead model
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the route key for model binding
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
