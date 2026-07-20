<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDepartment extends Model
{
    use SoftDeletes;

    protected $table = 'user_department';

    protected $fillable = [
        'department_id',
        'user_id',
    ];
}
