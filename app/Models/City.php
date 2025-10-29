<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';
    protected $fillable = ['name', 'country_id', 'state_id'];
    
    public $timestamps = true;
    
    // Relationship with country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    // Relationship with state
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
