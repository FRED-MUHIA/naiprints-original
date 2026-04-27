<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'category',
        'description',
        'amount',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'production_cost' => 'Production Cost',
            'materials_bought' => 'Materials Bought',
            'petty_cash' => 'Petty Cash',
            default => 'Other Cost',
        };
    }
}
