<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleCalender extends Model
{
    protected $fillable = [
        'user_id',
        'token',
    ];
}