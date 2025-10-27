<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use SoftDeletes;
    protected $table = 'agency';
    protected $fillable = ['name', 'slug', 'status', 'agency_group_id', 'agency_type_id'];
    protected $casts = ['deleted_at' => 'datetime'];

    // Agency ka group (optional)
    public function agencyGroup(): BelongsTo
    {
        return $this->belongsTo(AgencyGroup::class, 'agency_group_id');
    }

    // Agency ka type
    public function agencyType(): BelongsTo
    {
        return $this->belongsTo(AgencyType::class, 'agency_type_id');
    }

    // Agency ke brands
    public function brands(): HasMany
    {
        return $this->hasMany(AgencyBrand::class, 'agency_id');
    }
}
