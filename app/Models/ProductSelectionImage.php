<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSelectionImage extends Model
{
    protected $fillable = [
        'product_id',
        'label',
        'image_path',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
