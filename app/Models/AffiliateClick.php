<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    protected $fillable = [
        'affiliate_id',
        'referral_code',
        'ip_address',
        'user_agent',
        'referral_source',
        'landing_url',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
