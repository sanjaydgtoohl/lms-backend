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
        'meeting_start_date',
        'meeting_end_date',
        'status',
        'google_event',
    ];

    protected $casts = [
        'meeting_start_date' => 'datetime',
        'meeting_end_date' => 'datetime',
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

    /**
     * Get Google Event URL from JSON data
     */
    public function getGoogleEventUrl()
    {
        if (!$this->google_event) {
            return null;
        }

        try {
            $googleEvent = is_string($this->google_event) 
                ? json_decode($this->google_event, true) 
                : $this->google_event;
            
            return $googleEvent['html_link'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
