<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Agency;

class AgencyGroup extends Model
{
    use SoftDeletes;
    protected $table = 'agency_groups';
    protected $fillable = ['name', 'slug', 'status'];
    protected $casts = ['deleted_at' => 'datetime'];

    // Ek group mein kai agencies ho sakti hain
    public function agencies(): HasMany
    {
        return $this->hasMany(Agency::class, 'agency_group_id');
    }
}
