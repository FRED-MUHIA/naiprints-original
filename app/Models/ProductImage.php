<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\ClothingRailCache;

class ProductImage extends Model
{
    protected static function booted(): void
    {
        static::saved([ClothingRailCache::class, 'bust']);
        static::deleted([ClothingRailCache::class, 'bust']);
    }

    protected $fillable = [
        'product_id',
        'image_path',
        'position',
        'type',
        'view',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
