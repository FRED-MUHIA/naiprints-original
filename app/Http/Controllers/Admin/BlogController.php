<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $hasBlogsTable = Schema::hasTable('blogs');
        $hasCategoriesTable = Schema::hasTable('blog_categories');
        if (!$hasBlogsTable) {
            return view('admin.blogs.index', [
                'blogs' => collect(),
                'blogTablesReady' => false,
            ]);
        }

        $blogs = Blog::query()
            ->when($hasCategoriesTable, fn ($query) => $query->with('category'))
            ->orderByDesc('created_at')
            ->get();

        if (!$hasCategoriesTable) {
            $blogs = $blogs->map(function (Blog $blog) {
                $blog->setRelation('category', null);
                return $blog;
            });
        }

        return view('admin.blogs.index', [
            'blogs' => $blogs,
            'blogTablesReady' => true,
        ]);
    }

    public function create(): View
    {
        abort_unless(Schema::hasTable('blogs'), 404);
        $categories = Schema::hasTable('blog_categories')
            ? BlogCategory::orderBy('name')->get()
            : collect();

        return view('admin.blogs.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('blogs')) {
            return redirect()->route('admin.blogs.index')
                ->withErrors(['blog_tables' => 'Blog tables are missing. Run migrations first.']);
        }

        $hasCategoriesTable = Schema::hasTable('blog_categories');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/i'],
            'blog_category_id' => array_values(array_filter([
                'nullable',
                $hasCategoriesTable ? 'exists:blog_categories,id' : null,
            ])),
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
        if (!$hasCategoriesTable) {
            $data['blog_category_id'] = null;
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('blogs', 'public');
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created.');
    }

    public function edit(string $blog): View
    {
        abort_unless(Schema::hasTable('blogs'), 404);
        $blog = Blog::findOrFail($blog);
        $categories = Schema::hasTable('blog_categories')
            ? BlogCategory::orderBy('name')->get()
            : collect();
        if (!Schema::hasTable('blog_categories')) {
            $blog->setRelation('category', null);
        }

        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, string $blog): RedirectResponse
    {
        abort_unless(Schema::hasTable('blogs'), 404);
        $blog = Blog::findOrFail($blog);
        $hasCategoriesTable = Schema::hasTable('blog_categories');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/i'],
            'blog_category_id' => array_values(array_filter([
                'nullable',
                $hasCategoriesTable ? 'exists:blog_categories,id' : null,
            ])),
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
        if (!$hasCategoriesTable) {
            $data['blog_category_id'] = null;
        }

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

    public function destroy(string $blog): RedirectResponse
    {
        abort_unless(Schema::hasTable('blogs'), 404);
        $blog = Blog::findOrFail($blog);

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
