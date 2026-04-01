<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissCampaignHistory extends Model
{
    use HasFactory;

    protected $table = 'miss_campaign_histories';

    protected $fillable = [
        'miss_campaign_id',
        'status',
        'assign_by',
        'assign_to',
        'comment',
    ];

    // Relationships
    public function missCampaign()
    {
        return $this->belongsTo(MissCampaign::class);
    }

    public function assignBy()
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    public function assignTo()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }
}
