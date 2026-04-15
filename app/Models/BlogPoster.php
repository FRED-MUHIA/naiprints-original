<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BlogPoster extends Model
{
    protected $fillable = [
        'blog_id',
        'title',
        'image_path',
        'alt_text',
        'link_url',
        'open_in_new_tab',
        'is_active',
        'position',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }
}

