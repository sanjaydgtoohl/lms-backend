<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Add karein

class Country extends Model
{
    use HasFactory, SoftDeletes; // <-- Add karein
    
    public $incrementing = false; 
    protected $guarded = []; // Sabhi fields ko fillable banayein

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}