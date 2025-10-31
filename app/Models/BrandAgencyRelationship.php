<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // SoftDeletes ke liye

class BrandAgencyRelationship extends Model
{
    use HasFactory, SoftDeletes; // SoftDeletes trait ka use

    /**
     * The table associated with the model.
     * Table ka naam define kiya gaya hai
     *
     * @var string
     */
    protected $table = 'brand_agency_relationships';

    /**
     * The attributes that are mass assignable.
     * Sirf fillable fields yahan define kiye jaate hain.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agency_id',
        'brand_id',
    ];

    // Note: Agar aap is model ko seedha use karna chahte hain, toh aap 
    // yahan Brand aur Agency ke sath 'belongsTo' relationship define kar sakte hain.

    /**
     * Is relationship ka Brand model.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Is relationship ka Agency model.
     */
    public function agency()
    {
        // Yahan 'agency' table ke liye Agency model ka upyog karen.
        return $this->belongsTo(Agency::class, 'agency_id'); 
    }
}