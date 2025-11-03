<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use SoftDeletes;
    protected $table = 'zones';
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];
    public $timestamps = true;

    // Scope for active records
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }
}
