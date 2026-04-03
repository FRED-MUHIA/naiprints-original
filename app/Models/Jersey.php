<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jersey extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'jersey_type',
        'base_price',
        'model_url',
        'supports_sleeves',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'supports_sleeves' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function designs(): HasMany
    {
        return $this->hasMany(JerseyDesign::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(JerseyOrder::class);
    }
}
