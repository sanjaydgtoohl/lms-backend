<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'leads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand_id',
        'agency_id',
        'user_id', // Yahan 'user_id' hai (migration ke hisaab se)
        'status',
        'comment',
    ];

    /**
     * Ek Lead ke multiple contacts ho sakte hain.
     */
    
    public function contacts()
    {
        // Yeh batata hai ki 'Lead' model 'LeadContact' model se
        // 'lead_id' column ke zariye juda hua hai.
        return $this->hasMany(LeadContact::class, 'lead_id');
    }

    // --- Optional: Baaki relationships ---

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function brand()
    {
        // Maan rahe hain ki 'Brand' model hai
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function agency()
    {
        // Maan rahe hain ki 'Agency' model hai
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}