<?php

namespace App\Models;

use App\Support\PageDefaults;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function ensure(string $slug, string $title, array $defaultContent): self
    {
        $page = static::firstOrCreate(
            ['slug' => $slug],
            ['title' => $title, 'content' => $defaultContent]
        );

        if (!$page->content) {
            $page->update(['content' => $defaultContent]);
        }

        if ($slug === 'home') {
            $sections = $page->content['sections'] ?? [];
            $types = array_map(fn ($section) => $section['type'] ?? null, $sections);
            if (!in_array('collection_showcase', $types, true)) {
                $insert = PageDefaults::section('collection_showcase');
                $bestIndex = array_search('best_sellers', $types, true);
                if ($bestIndex === false) {
                    $sections[] = $insert;
                } else {
                    array_splice($sections, $bestIndex, 0, [$insert]);
                }

                $page->update(['content' => ['sections' => $sections]]);
            }
        }

        return $page;
    }
}
