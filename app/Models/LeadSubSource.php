<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadSubSource extends Model
{
    use SoftDeletes;

    protected $table = 'lead_sub_source';

    protected $fillable = [
        'lead_source_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // Relation: a sub-source belongs to a lead source
    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }
}
