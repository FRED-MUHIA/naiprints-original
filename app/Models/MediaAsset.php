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
        return $this->buildStorageUrl($this->file_path) ?? '';
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
