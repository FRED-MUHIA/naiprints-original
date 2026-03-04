<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'display_type',
        'description',
        'meta_description',
        'thumbnail_path',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return $this->buildStorageUrl($this->thumbnail_path);
    }

    private function buildStorageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, '//')) {
            return $path;
        }

        $normalized = '/'.ltrim($path, '/');
        $storagePath = str_starts_with($normalized, '/storage/')
            ? $normalized
            : '/storage/'.ltrim($path, '/');

        $basePath = '';
        if (app()->bound('request')) {
            try {
                $basePath = rtrim((string) request()->getBasePath(), '/');
            } catch (\Throwable) {
                $basePath = '';
            }
        }

        if ($basePath === '') {
            $basePath = rtrim((string) parse_url((string) config('app.url', ''), PHP_URL_PATH), '/');
        }

        return ($basePath ?: '').$storagePath;
    }
}
