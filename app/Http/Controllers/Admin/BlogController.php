<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $blogs = Blog::with('category')->orderByDesc('created_at')->get();

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blogs.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/i'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['title']);
        $data['published_at'] = $this->resolvePublishedAt($data);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('blogs', 'public');
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created.');
    }

    public function edit(Blog $blog): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/i'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'remove_cover_image' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['title'], $blog->id);
        $data['published_at'] = $this->resolvePublishedAt($data);

        if ($request->boolean('remove_cover_image') && $blog->cover_image_path) {
            Storage::disk('public')->delete($blog->cover_image_path);
            $data['cover_image_path'] = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($blog->cover_image_path) {
                Storage::disk('public')->delete($blog->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')->store('blogs', 'public');
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        if ($blog->cover_image_path) {
            Storage::disk('public')->delete($blog->cover_image_path);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted.');
    }

    private function resolvePublishedAt(array $data): ?string
    {
        if (($data['status'] ?? 'draft') !== 'published') {
            return null;
        }

        if (!empty($data['published_at'])) {
            return Carbon::parse((string) $data['published_at'])->toDateTimeString();
        }

        return now()->toDateTimeString();
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Blog::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
