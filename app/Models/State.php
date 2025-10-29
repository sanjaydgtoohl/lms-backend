<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'states';
    protected $fillable = ['name', 'country_id'];
    
    public $timestamps = true;
    
    // Relationship with country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    // Relationship with cities
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
