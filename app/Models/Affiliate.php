<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Affiliate extends Authenticatable
{
    use Notifiable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'payment_method',
        'payment_details',
        'status',
        'referral_code',
        'commission_type',
        'commission_value',
        'approved_at',
        'rejected_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function ensureReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        do {
            $code = Str::upper(Str::slug(Str::before($this->full_name, ' ')) ?: 'AFF') . random_int(1000, 9999);
        } while (static::query()->where('referral_code', $code)->whereKeyNot($this->id)->exists());

        $this->forceFill(['referral_code' => $code])->save();

        return $code;
    }

    public function getReferralUrlAttribute(): string
    {
        $code = $this->referral_code ?: $this->ensureReferralCode();

        return url('/?ref=' . urlencode($code));
    }

    public function commissionType(): string
    {
        return $this->commission_type ?: AffiliateSetting::current()->commission_type;
    }

    public function commissionValue(): float
    {
        return (float) ($this->commission_value ?? AffiliateSetting::current()->commission_value);
    }
}
