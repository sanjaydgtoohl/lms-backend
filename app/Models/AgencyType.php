<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyType extends Model
{
    use SoftDeletes;
    protected $table = 'agency_type';
    protected $fillable = ['name', 'slug', 'status'];
    protected $casts = ['deleted_at' => 'datetime'];
}
