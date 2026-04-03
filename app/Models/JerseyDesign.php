<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JerseyDesign extends Model
{
    protected $fillable = [
        'public_id',
        'user_id',
        'jersey_id',
        'design_name',
        'is_autosave',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'is_autosave' => 'boolean',
            'payload' => 'array',
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

    public function orders(): HasMany
    {
        return $this->hasMany(JerseyOrder::class);
    }
}
