<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyGroup extends Model
{
    use HasFactory, SoftDeletes;

    // The table name 'agency_groups' matches the model name 'AgencyGroup'
    // so you don't strictly need to set $table, but it's good practice.
    protected $table = 'agency_groups';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];
}