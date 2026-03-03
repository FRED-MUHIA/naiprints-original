<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $names = preg_split('/[,\n]+/', (string) $data['name']);
        $names = array_filter(array_map('trim', $names), fn ($value) => $value !== '');
        $names = array_values(array_unique($names));

        $created = 0;
        $skipped = 0;

        foreach ($names as $name) {
            $exists = Tag::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            Tag::create([
                'name' => $name,
                'slug' => $this->makeUniqueSlug($name),
            ]);
            $created++;
        }

        return redirect()->route('admin.tags.index')
            ->with('success', $created > 1
                ? "Created {$created} tags." . ($skipped ? " Skipped {$skipped} existing." : '')
                : ($created === 1 ? 'Tag created.' : 'No new tags created.')
            );
    }

    /**
     * Display the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['name'], $tag->id);

        $tag->update($data);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted.');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Tag::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
