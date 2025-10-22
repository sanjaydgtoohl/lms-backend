<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Industry extends Model
{
    use SoftDeletes;

    protected $table = 'industries';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'status' => 'integer',
    ];
}