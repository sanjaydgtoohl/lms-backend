<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Agency;

class AgencyBrand extends Model
{
    use SoftDeletes;
    protected $table = 'agency_brand';
    protected $fillable = ['name', 'slug', 'status', 'agency_id'];
    protected $casts = ['deleted_at' => 'datetime'];

    // Har brand ek agency se juda hai
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}
