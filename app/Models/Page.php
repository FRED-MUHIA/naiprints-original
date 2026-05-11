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
            $updated = false;

            if (!in_array('collection_showcase', $types, true)) {
                $insert = PageDefaults::section('collection_showcase');
                $bestIndex = array_search('best_sellers', $types, true);
                if ($bestIndex === false) {
                    $sections[] = $insert;
                } else {
                    array_splice($sections, $bestIndex, 0, [$insert]);
                }
                $types = array_map(fn ($section) => $section['type'] ?? null, $sections);
                $updated = true;
            }

            if (!in_array('product_steps', $types, true)) {
                $insert = PageDefaults::section('product_steps');
                $categoryIndex = array_search('category_carousel', $types, true);
                if ($categoryIndex === false) {
                    $sections[] = $insert;
                } else {
                    array_splice($sections, $categoryIndex + 1, 0, [$insert]);
                }
                $types = array_map(fn ($section) => $section['type'] ?? null, $sections);
                $updated = true;
            }

            $productStepsIndex = array_search('product_steps', $types, true);
            $categoryIndex = array_search('category_carousel', $types, true);
            if ($productStepsIndex !== false && $categoryIndex !== false && $productStepsIndex < $categoryIndex) {
                $productStepsSection = $sections[$productStepsIndex];
                array_splice($sections, $productStepsIndex, 1);
                $types = array_map(fn ($section) => $section['type'] ?? null, $sections);
                $categoryIndex = array_search('category_carousel', $types, true);
                array_splice($sections, $categoryIndex + 1, 0, [$productStepsSection]);
                $types = array_map(fn ($section) => $section['type'] ?? null, $sections);
                $updated = true;
            }

            if (!in_array('sale_feature', $types, true)) {
                $insert = PageDefaults::section('sale_feature');
                $bestIndex = array_search('best_sellers', $types, true);
                if ($bestIndex === false) {
                    $sections[] = $insert;
                } else {
                    array_splice($sections, $bestIndex + 1, 0, [$insert]);
                }
                $types = array_map(fn ($section) => $section['type'] ?? null, $sections);
                $updated = true;
            }

            if (!in_array('footer_notice', $types, true)) {
                $sections[] = PageDefaults::section('footer_notice');
                $updated = true;
            }

            if ($updated) {
                $page->update(['content' => ['sections' => $sections]]);
            }
        }

        return $page;
    }
}
