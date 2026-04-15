<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogPoster;
use App\Models\GlobalSection;
use App\Support\PageDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());

        $selectedCategorySlug = trim((string) $request->query('category', ''));
        $categories = BlogCategory::withCount([
            'blogs as published_blogs_count' => fn ($query) => $query->published(),
        ])->orderBy('name')->get();

        $selectedCategory = null;
        if ($selectedCategorySlug !== '') {
            $selectedCategory = $categories->first(function (BlogCategory $category) use ($selectedCategorySlug) {
                return strtolower((string) $category->slug) === strtolower($selectedCategorySlug);
            });
        }

        $blogs = Blog::with('category')
            ->published()
            ->when($selectedCategory, fn ($query) => $query->where('blog_category_id', $selectedCategory->id))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        $metaTitle = $selectedCategory
            ? ($selectedCategory->name . ' Blogs | Nai Prints')
            : 'Blog | Nai Prints';
        $metaDescription = $selectedCategory
            ? ('Read the latest "' . $selectedCategory->name . '" insights, tips, and stories from Nai Prints.')
            : 'Read the latest custom printing tips, branding ideas, and design stories from Nai Prints.';

        return view('blog.index', [
            'blogs' => $blogs,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'metaTitle' => $metaTitle,
            'metaDescription' => Str::limit($metaDescription, 155, ''),
            'globalHeader' => $header->content,
            'globalFooter' => $footer->content,
            'globalPurchasePopups' => $purchasePopups->content,
            'globalPurchasePopupProducts' => [],
        ]);
    }

    public function show(Blog $blog): View
    {
        abort_unless(
            $blog->status === 'published' && ($blog->published_at === null || $blog->published_at->lte(now())),
            404
        );

        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
        $blog->load('category');

        $posters = BlogPoster::where('is_active', true)
            ->where(function ($query) use ($blog) {
                $query->whereNull('blog_id')
                    ->orWhere('blog_id', $blog->id);
            })
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $alternativeBlogs = Blog::with('category')
            ->published()
            ->where('id', '!=', $blog->id)
            ->when($blog->blog_category_id, fn ($query) => $query->where('blog_category_id', $blog->blog_category_id))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        if ($alternativeBlogs->count() < 5) {
            $moreBlogs = Blog::with('category')
                ->published()
                ->where('id', '!=', $blog->id)
                ->whereNotIn('id', $alternativeBlogs->pluck('id'))
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->take(5 - $alternativeBlogs->count())
                ->get();
            $alternativeBlogs = $alternativeBlogs->concat($moreBlogs);
        }

        $defaultMetaDescription = $blog->excerpt ?: Str::limit(strip_tags($blog->content), 155, '');

        return view('blog.show', [
            'blog' => $blog,
            'posters' => $posters,
            'alternativeBlogs' => $alternativeBlogs,
            'metaTitle' => $blog->meta_title ?: ($blog->title . ' | Nai Prints Blog'),
            'metaDescription' => $blog->meta_description ?: $defaultMetaDescription,
            'globalHeader' => $header->content,
            'globalFooter' => $footer->content,
            'globalPurchasePopups' => $purchasePopups->content,
            'globalPurchasePopupProducts' => [],
        ]);
    }
}

