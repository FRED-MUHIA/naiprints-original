<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Carbon\Carbon;
use DOMElement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    public function create(): View|RedirectResponse
    {
        if (!Schema::hasTable('blogs')) {
            return redirect()->route('admin.blogs.index')
                ->withErrors(['blog_tables' => 'Blog tables are missing. Run migrations first.']);
        }
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
        $data['content'] = $this->prepareContent((string) $data['content']);
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
        $blog = $this->resolveBlog($blog);
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
        $blog = $this->resolveBlog($blog);
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
        $data['content'] = $this->prepareContent((string) $data['content']);
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
        $blog = $this->resolveBlog($blog);

        if ($blog->cover_image_path) {
            Storage::disk('public')->delete($blog->cover_image_path);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted.');
    }

    public function publish(string $blog): RedirectResponse
    {
        abort_unless(Schema::hasTable('blogs'), 404);
        $blog = $this->resolveBlog($blog);

        if ($blog->status !== 'published') {
            $blog->status = 'published';
            $blog->published_at = $blog->published_at ?: now();
            $blog->save();
        }

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post published.');
    }

    public function uploadEditorImage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:10240'],
        ]);

        $path = $data['file']->store('blogs/content', 'public');

        return response()->json([
            'location' => $this->publicMediaUrl($path),
        ]);
    }

    public function uploadEditorImageUrl(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $path = $this->storeRemoteImage((string) $data['url']);
        if (!$path) {
            return response()->json([
                'message' => 'Could not fetch image from the provided URL.',
            ], 422);
        }

        return response()->json([
            'location' => $this->publicMediaUrl($path),
        ]);
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

    private function sanitizeContent(string $content): string
    {
        $content = preg_replace('#<\s*script[^>]*>.*?<\s*/\s*script>#is', '', $content) ?? $content;
        $content = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $content) ?? $content;
        $content = preg_replace('/\s(href|src)\s*=\s*("|\')\s*javascript:.*?\2/i', ' $1="#"', $content) ?? $content;

        return trim($content);
    }

    private function prepareContent(string $content): string
    {
        $content = $this->sanitizeContent($content);
        return $this->localizeEmbeddedImages($content);
    }

    private function localizeEmbeddedImages(string $content): string
    {
        if (!str_contains(strtolower($content), '<img')) {
            return $content;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . $content,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return $content;
        }

        $images = $dom->getElementsByTagName('img');
        $toProcess = [];
        foreach ($images as $image) {
            $toProcess[] = $image;
        }

        foreach ($toProcess as $image) {
            $src = $this->resolveImageSource($image);
            if (!$src) {
                continue;
            }

            $image->setAttribute('src', $src);

            $storedPath = null;
            if (preg_match('/^data:image\//i', $src) === 1) {
                $storedPath = $this->storeDataUriImage($src);
            } elseif (preg_match('#^https?://#i', $src) === 1 || str_starts_with($src, '//')) {
                $url = str_starts_with($src, '//') ? ('https:' . $src) : $src;
                $storedPath = $this->storeRemoteImage($url);
            }

            if ($storedPath) {
                $image->setAttribute('src', $this->publicMediaUrl($storedPath));
                if ($image->hasAttribute('srcset')) {
                    $image->removeAttribute('srcset');
                }
                foreach (['data-src', 'data-lazy-src', 'data-original', 'data-srcset'] as $attr) {
                    if ($image->hasAttribute($attr)) {
                        $image->removeAttribute($attr);
                    }
                }
            }
        }

        $html = $dom->saveHTML() ?: $content;
        $html = preg_replace('/^<\?xml[^>]+>\s*/', '', $html) ?? $html;

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($html);
    }

    private function storeDataUriImage(string $src): ?string
    {
        if (!preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,(.+)$/s', $src, $matches)) {
            return null;
        }

        $rawType = strtolower((string) ($matches[1] ?? ''));
        $data = str_replace(' ', '+', (string) ($matches[2] ?? ''));
        $binary = base64_decode($data, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        if (strlen($binary) > 10 * 1024 * 1024) {
            return null;
        }

        $extension = $this->resolveImageExtension($rawType);
        if (!$extension) {
            return null;
        }

        $path = 'blogs/content/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function storeRemoteImage(string $url): ?string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->ok()) {
            return null;
        }

        $body = $response->body();
        if ($body === '') {
            return null;
        }

        if (strlen($body) > 10 * 1024 * 1024) {
            return null;
        }

        $contentType = strtolower(trim((string) $response->header('Content-Type')));
        $contentType = explode(';', $contentType)[0] ?? $contentType;
        $extension = $this->resolveImageExtension($contentType);

        if (!$extension) {
            $path = parse_url($url, PHP_URL_PATH);
            $pathExtension = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'];
            $extension = in_array($pathExtension, $allowed, true) ? $pathExtension : null;
        }

        if (!$extension) {
            return null;
        }

        $path = 'blogs/content/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $body);

        return $path;
    }

    private function resolveImageExtension(string $mimeOrSubtype): ?string
    {
        $value = strtolower(trim($mimeOrSubtype));
        $map = [
            'image/jpeg' => 'jpg',
            'jpeg' => 'jpg',
            'jpg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'png' => 'png',
            'image/gif' => 'gif',
            'gif' => 'gif',
            'image/webp' => 'webp',
            'webp' => 'webp',
            'image/svg+xml' => 'svg',
            'svg+xml' => 'svg',
            'svg' => 'svg',
            'image/avif' => 'avif',
            'avif' => 'avif',
        ];

        return $map[$value] ?? null;
    }

    private function resolveImageSource(DOMElement $image): ?string
    {
        $attributeCandidates = [
            'src',
            'data-src',
            'data-lazy-src',
            'data-original',
            'data-orig-src',
            'data-image',
            'data-actualsrc',
            'data-srcset',
            'srcset',
        ];

        $best = null;
        foreach ($attributeCandidates as $attribute) {
            if (!$image->hasAttribute($attribute)) {
                continue;
            }
            $value = trim((string) $image->getAttribute($attribute));
            if ($value === '') {
                continue;
            }

            if (str_contains($attribute, 'srcset')) {
                $value = trim((string) preg_split('/[\s,]+/', $value)[0]);
            }
            if ($value === '') {
                continue;
            }

            if ($attribute === 'src') {
                $best = $value;
                if (!$this->isLikelyPlaceholderImage($value)) {
                    return $value;
                }
                continue;
            }

            if ($best === null || $this->isLikelyPlaceholderImage($best)) {
                $best = $value;
            }
        }

        return $best ? trim($best) : null;
    }

    private function isLikelyPlaceholderImage(string $value): bool
    {
        $value = strtolower(trim($value));
        if ($value === '' || $value === '#' || $value === 'about:blank') {
            return true;
        }

        if (str_starts_with($value, 'data:image/gif;base64,r0lgod')) {
            return true;
        }

        return false;
    }

    private function publicMediaUrl(string $path): string
    {
        if (\Illuminate\Support\Facades\Route::has('blogs.media')) {
            return route('blogs.media', ['path' => ltrim($path, '/')]);
        }

        return Storage::url($path);
    }

    private function resolveBlog(string $value): Blog
    {
        $query = Blog::query();

        if (is_numeric($value)) {
            $query->where('id', (int) $value);
        } else {
            $query->where('slug', $value);
        }

        return $query->firstOrFail();
    }
}
