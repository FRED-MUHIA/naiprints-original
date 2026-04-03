<?php

namespace App\Http\Controllers;

use App\Models\Jersey;
use Illuminate\Http\JsonResponse;

class JerseyCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $this->ensureDefaults();

        $jerseys = Jersey::query()
            ->orderBy('id')
            ->get([
                'id',
                'slug',
                'name',
                'jersey_type',
                'base_price',
                'model_url',
                'supports_sleeves',
                'metadata',
            ]);

        return response()->json([
            'data' => $jerseys,
        ]);
    }

    private function ensureDefaults(): void
    {
        if (Jersey::query()->exists()) {
            return;
        }

        $defaults = config('jersey.defaults', []);
        foreach ($defaults as $item) {
            Jersey::query()->create([
                'slug' => $item['slug'] ?? null,
                'name' => $item['name'] ?? 'Jersey',
                'jersey_type' => $item['jersey_type'] ?? 'football_short',
                'base_price' => $item['base_price'] ?? 0,
                'model_url' => $item['model_url'] ?? null,
                'supports_sleeves' => (bool) ($item['supports_sleeves'] ?? true),
                'metadata' => $item['metadata'] ?? [],
            ]);
        }
    }
}
