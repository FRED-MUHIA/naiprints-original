<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogPoster;
use App\Models\GlobalSection;
use App\Support\PageDefaults;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
        $hasBlogsTable = Schema::hasTable('blogs');
        $hasBlogCategoriesTable = Schema::hasTable('blog_categories');

        $selectedCategorySlug = trim((string) $request->query('category', ''));
        if ($hasBlogCategoriesTable && $hasBlogsTable) {
            $categories = BlogCategory::withCount([
                'blogs as published_blogs_count' => fn ($query) => $query->published(),
            ])->orderBy('name')->get();
        } elseif ($hasBlogCategoriesTable) {
            $categories = BlogCategory::orderBy('name')->get()->map(function (BlogCategory $category) {
                $category->setAttribute('published_blogs_count', 0);
                return $category;
            });
        } else {
            $categories = collect();
        }

        $selectedCategory = null;
        if ($selectedCategorySlug !== '') {
            $selectedCategory = $categories->first(function (BlogCategory $category) use ($selectedCategorySlug) {
                return strtolower((string) $category->slug) === strtolower($selectedCategorySlug);
            });
        }

        if ($hasBlogsTable) {
            $blogsQuery = Blog::query()
                ->when($hasBlogCategoriesTable, fn ($query) => $query->with('category'))
                ->published()
                ->when(
                    $selectedCategory && $hasBlogCategoriesTable,
                    fn ($query) => $query->where('blog_category_id', $selectedCategory->id)
                )
                ->orderByDesc('published_at')
                ->orderByDesc('created_at');

            $blogs = $blogsQuery->paginate(9)->withQueryString();
            if (!$hasBlogCategoriesTable) {
                $blogs->getCollection()->transform(function (Blog $blog) {
                    $blog->setRelation('category', null);
                    return $blog;
                });
            }
        } else {
            $blogs = $this->emptyBlogPaginator($request);
        }

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

    public function show(string $slug): View
    {
        $hasBlogsTable = Schema::hasTable('blogs');
        $hasBlogCategoriesTable = Schema::hasTable('blog_categories');
        $hasBlogPostersTable = Schema::hasTable('blog_posters');
        abort_unless($hasBlogsTable, 404);

        $blogQuery = Blog::query()
            ->when($hasBlogCategoriesTable, fn ($query) => $query->with('category'))
            ->published()
            ->where('slug', $slug);
        $blog = $blogQuery->firstOrFail();
        if (!$hasBlogCategoriesTable) {
            $blog->setRelation('category', null);
        }

        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());

        if ($hasBlogPostersTable) {
            $posters = BlogPoster::where('is_active', true)
                ->where(function ($query) use ($blog) {
                    $query->whereNull('blog_id')
                        ->orWhere('blog_id', $blog->id);
                })
                ->orderBy('position')
                ->orderByDesc('created_at')
                ->take(8)
                ->get();
        } else {
            $posters = collect();
        }

        $alternativeBlogs = Blog::query()
            ->when($hasBlogCategoriesTable, fn ($query) => $query->with('category'))
            ->published()
            ->where('id', '!=', $blog->id)
            ->when(
                $hasBlogCategoriesTable && $blog->blog_category_id,
                fn ($query) => $query->where('blog_category_id', $blog->blog_category_id)
            )
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        if ($alternativeBlogs->count() < 5) {
            $moreBlogs = Blog::query()
                ->when($hasBlogCategoriesTable, fn ($query) => $query->with('category'))
                ->published()
                ->where('id', '!=', $blog->id)
                ->whereNotIn('id', $alternativeBlogs->pluck('id'))
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->take(5 - $alternativeBlogs->count())
                ->get();
            $alternativeBlogs = $alternativeBlogs->concat($moreBlogs);
        }
        if (!$hasBlogCategoriesTable) {
            $alternativeBlogs = $alternativeBlogs->map(function (Blog $alternativeBlog) {
                $alternativeBlog->setRelation('category', null);
                return $alternativeBlog;
            });
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

    private function emptyBlogPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            9,
            $request->integer('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
