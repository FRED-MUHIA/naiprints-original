<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogPoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BlogPosterController extends Controller
{
    public function index(): View
    {
        $posters = BlogPoster::with('blog')->orderBy('position')->orderByDesc('created_at')->get();

        return view('admin.blog-posters.index', compact('posters'));
    }

    public function create(): View
    {
        $blogs = Blog::orderByDesc('created_at')->get();

        return view('admin.blog-posters.create', compact('blogs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'blog_id' => ['nullable', 'exists:blogs,id'],
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
        $data['image_path'] = $request->file('image')->store('blog-posters', 'public');

        BlogPoster::create($data);

        return redirect()->route('admin.blog-posters.index')
            ->with('success', 'Blog poster created.');
    }

    public function edit(BlogPoster $blogPoster): View
    {
        $blogs = Blog::orderByDesc('created_at')->get();

        return view('admin.blog-posters.edit', compact('blogPoster', 'blogs'));
    }

    public function update(Request $request, BlogPoster $blogPoster): RedirectResponse
    {
        $data = $request->validate([
            'blog_id' => ['nullable', 'exists:blogs,id'],
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

    public function destroy(BlogPoster $blogPoster): RedirectResponse
    {
        if ($blogPoster->image_path) {
            Storage::disk('public')->delete($blogPoster->image_path);
        }

        $blogPoster->delete();

        return redirect()->route('admin.blog-posters.index')
            ->with('success', 'Blog poster deleted.');
    }
}
