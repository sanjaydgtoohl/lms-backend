<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlannerHistory extends BaseModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'planner_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'planner_id',
        'brief_id',
        'created_by',
        'planner_status_id',
        'submitted_plan',
        'backup_plan',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_plan' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: A planner history belongs to a planner.
     */
    public function planner()
    {
        return $this->belongsTo(Planner::class);
    }

    /**
     * Relationship: A planner history has a planner status.
     */
    public function plannerStatus()
    {
        return $this->belongsTo(PlannerStatus::class, 'planner_status_id');
    }

    /**
     * Relationship: A planner history belongs to a brief.
     */
    public function brief()
    {
        return $this->belongsTo(Brief::class);
    }

    /**
     * Relationship: A planner history was created by a user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
