<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JerseyOrder extends Model
{
    protected $fillable = [
        'public_id',
        'user_id',
        'jersey_id',
        'jersey_design_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'sizes',
        'pricing',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sizes' => 'array',
            'pricing' => 'array',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jersey(): BelongsTo
    {
        return $this->belongsTo(Jersey::class);
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(JerseyDesign::class, 'jersey_design_id');
    }
}
