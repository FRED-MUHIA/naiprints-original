<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogPoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BlogPosterController extends Controller
{
    public function index(): View
    {
        $hasPostersTable = Schema::hasTable('blog_posters');
        $hasBlogsTable = Schema::hasTable('blogs');
        if (!$hasPostersTable) {
            return view('admin.blog-posters.index', [
                'posters' => collect(),
                'blogTablesReady' => false,
            ]);
        }

        $posters = BlogPoster::query()
            ->when($hasBlogsTable, fn ($query) => $query->with('blog'))
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get();
        if (!$hasBlogsTable) {
            $posters = $posters->map(function (BlogPoster $poster) {
                $poster->setRelation('blog', null);
                return $poster;
            });
        }

        return view('admin.blog-posters.index', [
            'posters' => $posters,
            'blogTablesReady' => true,
        ]);
    }

    public function create(): View
    {
        abort_unless(Schema::hasTable('blog_posters'), 404);
        $blogs = Schema::hasTable('blogs')
            ? Blog::orderByDesc('created_at')->get()
            : collect();

        return view('admin.blog-posters.create', compact('blogs'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('blog_posters')) {
            return redirect()->route('admin.blog-posters.index')
                ->withErrors(['blog_tables' => 'Blog tables are missing. Run migrations first.']);
        }

        $hasBlogsTable = Schema::hasTable('blogs');
        $data = $request->validate([
            'blog_id' => array_values(array_filter([
                'nullable',
                $hasBlogsTable ? 'exists:blogs,id' : null,
            ])),
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['position'] = is_numeric($data['position'] ?? null) ? (int) $data['position'] : 0;
        if (!$hasBlogsTable) {
            $data['blog_id'] = null;
        }
        $data['image_path'] = $request->file('image')->store('blog-posters', 'public');

        BlogPoster::create($data);

        return redirect()->route('admin.blog-posters.index')
            ->with('success', 'Blog poster created.');
    }

    public function edit(string $blogPoster): View
    {
        abort_unless(Schema::hasTable('blog_posters'), 404);
        $blogPoster = BlogPoster::findOrFail($blogPoster);
        $blogs = Schema::hasTable('blogs')
            ? Blog::orderByDesc('created_at')->get()
            : collect();

        return view('admin.blog-posters.edit', compact('blogPoster', 'blogs'));
    }

    public function update(Request $request, string $blogPoster): RedirectResponse
    {
        abort_unless(Schema::hasTable('blog_posters'), 404);
        $blogPoster = BlogPoster::findOrFail($blogPoster);
        $hasBlogsTable = Schema::hasTable('blogs');

        $data = $request->validate([
            'blog_id' => array_values(array_filter([
                'nullable',
                $hasBlogsTable ? 'exists:blogs,id' : null,
            ])),
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['position'] = is_numeric($data['position'] ?? null) ? (int) $data['position'] : 0;
        if (!$hasBlogsTable) {
            $data['blog_id'] = null;
        }

        if ($request->hasFile('image')) {
            if ($blogPoster->image_path) {
                Storage::disk('public')->delete($blogPoster->image_path);
            }
            $data['image_path'] = $request->file('image')->store('blog-posters', 'public');
        }

        $blogPoster->update($data);

        return redirect()->route('admin.blog-posters.index')
            ->with('success', 'Blog poster updated.');
    }

    public function destroy(string $blogPoster): RedirectResponse
    {
        abort_unless(Schema::hasTable('blog_posters'), 404);
        $blogPoster = BlogPoster::findOrFail($blogPoster);

        if ($blogPoster->image_path) {
            Storage::disk('public')->delete($blogPoster->image_path);
        }

        $blogPoster->delete();

        return redirect()->route('admin.blog-posters.index')
            ->with('success', 'Blog poster deleted.');
    }
}
