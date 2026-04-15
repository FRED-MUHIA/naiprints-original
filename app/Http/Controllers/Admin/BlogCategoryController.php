<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        $categories = BlogCategory::withCount('blogs')->orderBy('name')->get();

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.blog-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/i'],
            'description' => ['nullable', 'string'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['name']);

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category created.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.blog-categories.edit', compact('blogCategory'));
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/i'],
            'description' => ['nullable', 'string'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['name'], $blogCategory->id);

        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category deleted.');
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;

        while (
            BlogCategory::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

