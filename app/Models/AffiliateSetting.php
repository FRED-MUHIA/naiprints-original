<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateSetting extends Model
{
    protected $fillable = [
        'commission_type',
        'commission_value',
        'minimum_payout_amount',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'minimum_payout_amount' => 'decimal:2',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'minimum_payout_amount' => 1000,
        ]);
    }
}
