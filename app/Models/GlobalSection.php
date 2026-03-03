<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalSection extends Model
{
    protected $fillable = [
        'key',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public static function ensure(string $key, array $defaultContent): self
    {
        $section = static::firstOrCreate(
            ['key' => $key],
            ['content' => $defaultContent]
        );

        if (!$section->content) {
            $section->update(['content' => $defaultContent]);
        }

        return $section;
    }
}
