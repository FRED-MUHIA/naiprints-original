<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'color_options',
        'image_path',
        'description',
        'meta_title',
        'meta_description',
        'price',
        'discount_type',
        'discount_value',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'stock' => 'integer',
        'color_options' => 'array',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return $this->buildStorageUrl($this->image_path);
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->where('type', 'gallery')
            ->orderBy('position');
    }

    public function alternateImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->where('type', 'alternate')
            ->orderBy('position');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)
            ->withPivot('value')
            ->withTimestamps();
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class)
            ->withPivot(['price', 'stock'])
            ->withTimestamps();
    }
}
