<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use App\Support\ClothingRailCache;

class Product extends Model
{
    protected static function booted(): void
    {
        static::saved([ClothingRailCache::class, 'bust']);
        static::deleted([ClothingRailCache::class, 'bust']);
    }

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'color_options',
        'image_path',
        'rail_hanger_style',
        'rail_image_overlap',
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
        'rail_image_overlap' => 'integer',
        'color_options' => 'array',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($this->image_path);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
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

    public function railImages(): HasMany
    {
        $relation = $this->hasMany(ProductImage::class);

        if (Schema::hasColumn('product_images', 'view')) {
            $relation->whereIn('view', ['side', 'back']);
        } else {
            $relation->whereRaw('1 = 0');
        }

        return $relation->orderBy('position');
    }

    public function selectionImages(): HasMany
    {
        return $this->hasMany(ProductSelectionImage::class)->orderBy('position');
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
