<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = [
        'file_path',
        'title',
        'alt_text',
    ];

    public function getUrlAttribute(): string
    {
        return StorageUrl::fromPath($this->file_path) ?? '';
    }
}
