<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        $hasCategoriesTable = Schema::hasTable('blog_categories');
        $hasBlogsTable = Schema::hasTable('blogs');

        if (!$hasCategoriesTable) {
            return view('admin.blog-categories.index', [
                'categories' => collect(),
                'blogTablesReady' => false,
            ]);
        }

        if ($hasBlogsTable) {
            $categories = BlogCategory::withCount('blogs')->orderBy('name')->get();
        } else {
            $categories = BlogCategory::orderBy('name')->get()->map(function (BlogCategory $category) {
                $category->setAttribute('blogs_count', 0);
                return $category;
            });
        }

        return view('admin.blog-categories.index', [
            'categories' => $categories,
            'blogTablesReady' => true,
        ]);
    }

    public function create(): View
    {
        return view('admin.blog-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('blog_categories')) {
            return redirect()->route('admin.blog-categories.index')
                ->withErrors(['blog_tables' => 'Blog tables are missing. Run migrations first.']);
        }

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

    public function edit(string $blogCategory): View
    {
        abort_unless(Schema::hasTable('blog_categories'), 404);
        $blogCategory = BlogCategory::findOrFail($blogCategory);

        return view('admin.blog-categories.edit', compact('blogCategory'));
    }

    public function update(Request $request, string $blogCategory): RedirectResponse
    {
        abort_unless(Schema::hasTable('blog_categories'), 404);
        $blogCategory = BlogCategory::findOrFail($blogCategory);

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

    public function destroy(string $blogCategory): RedirectResponse
    {
        abort_unless(Schema::hasTable('blog_categories'), 404);
        $blogCategory = BlogCategory::findOrFail($blogCategory);

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
