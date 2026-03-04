<?php

namespace App\Models;

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
        return \Illuminate\Support\Facades\Storage::url($this->file_path);
    }
}
