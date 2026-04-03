<?php

namespace App\Http\Controllers;

use App\Models\JerseyDesign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class JerseyDesignController extends Controller
{
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'design_id' => ['nullable', 'integer'],
            'jersey_id' => ['nullable', 'integer', 'exists:jerseys,id'],
            'design_name' => ['nullable', 'string', 'max:160'],
            'jersey_type' => ['required', 'string', 'max:80'],
            'colors' => ['required', 'array'],
            'patterns' => ['nullable', 'array'],
            'text_layers' => ['nullable', 'array'],
            'logos' => ['nullable', 'array'],
            'positions' => ['nullable', 'array'],
            'artboards' => ['nullable', 'array'],
            'extras' => ['nullable', 'array'],
            'autosave' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $design = null;

        if (!empty($validated['design_id'])) {
            $design = JerseyDesign::query()->find($validated['design_id']);
            if ($design && $design->user_id && (!$user || $design->user_id !== $user->id)) {
                abort(403, 'You cannot edit this design.');
            }
        }

        $payload = [
            'jersey_type' => $validated['jersey_type'],
            'colors' => $validated['colors'],
            'patterns' => $validated['patterns'] ?? [],
            'text_layers' => $validated['text_layers'] ?? [],
            'logos' => $validated['logos'] ?? [],
            'positions' => $validated['positions'] ?? [],
            'artboards' => $validated['artboards'] ?? [],
            'extras' => $validated['extras'] ?? [],
        ];

        if (!$design) {
            $design = new JerseyDesign();
            $design->public_id = (string) Str::uuid();
        }

        $design->user_id = $user?->id;
        $design->jersey_id = $validated['jersey_id'] ?? null;
        $design->design_name = $validated['design_name'] ?? null;
        $design->is_autosave = (bool) ($validated['autosave'] ?? false);
        $design->payload = $payload;
        $design->save();

        return response()->json([
            'message' => 'Design saved successfully.',
            'data' => [
                'id' => $design->id,
                'public_id' => $design->public_id,
                'saved_at' => $design->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $query = JerseyDesign::query();
        $design = $query
            ->where(function ($innerQuery) use ($id) {
                if (ctype_digit($id)) {
                    $innerQuery->orWhere('id', (int) $id);
                }
                $innerQuery->orWhere('public_id', $id);
            })
            ->firstOrFail();

        $user = $request->user();
        if ($design->user_id && (!$user || $design->user_id !== $user->id)) {
            abort(403, 'You cannot view this design.');
        }

        return response()->json([
            'data' => [
                'id' => $design->id,
                'public_id' => $design->public_id,
                'jersey_id' => $design->jersey_id,
                'design_name' => $design->design_name,
                'is_autosave' => $design->is_autosave,
                'payload' => $design->payload,
                'updated_at' => $design->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
