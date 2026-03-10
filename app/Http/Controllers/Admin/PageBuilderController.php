<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GlobalSection;
use App\Models\Page;
use App\Models\Product;
use App\Support\PageDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageBuilderController extends Controller
{
    public function index()
    {
        $pages = $this->ensureDefaults();
        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
        $popupProducts = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.builder.index', [
            'pages' => $pages,
            'header' => $header,
            'footer' => $footer,
            'purchasePopups' => $purchasePopups,
            'popupProducts' => $popupProducts,
        ]);
    }

    public function edit(Page $page)
    {
        $this->ensureDefaults();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.builder.edit', [
            'page' => $page,
            'categories' => $categories,
        ]);
    }

    public function update(Page $page, Request $request)
    {
        $validator = validator($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sections' => ['array'],
            'sections.*.data.tiles.*.background_image' => ['nullable', 'string', 'max:2048'],
            'sections.*.data.tiles.*.background_image_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.tiles.*.background_image_alt' => ['nullable', 'string', 'max:2048'],
            'sections.*.data.tiles.*.background_image_alt_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.tiles.*.video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:20480'],
            'sections.*.data.tiles.*.video_url' => ['nullable', 'string', 'max:2048'],
            'sections.*.data.background_image' => ['nullable', 'string', 'max:2048'],
            'sections.*.data.background_image_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.hero.image' => ['nullable', 'string', 'max:2048'],
            'sections.*.data.hero.image_alt' => ['nullable', 'string', 'max:255'],
            'sections.*.data.hero.image_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.brand_logo_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.templates.all_results.*.image_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.mockups.*.*_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.mockups.*.*.*_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.mockups.*.*.*.*_file' => ['nullable', 'image', 'max:4096'],
            'sections.*.data.product_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sections.*.data.gift_finder.product_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'add_section_type' => ['nullable', 'string', 'max:50'],
        ]);
        $validator->excludeUnvalidatedArrayKeys = false;
        $data = $validator->validate();

        $currentSections = $page->content['sections'] ?? [];
        $inputSections = $request->input('sections', []);
        if (!is_array($inputSections)) {
            $inputSections = [];
        }
        if (!empty($inputSections)) {
            $sections = array_replace_recursive($currentSections, $inputSections);
        } else {
            $sections = $currentSections;
        }
        if (!empty($data['add_section_type'])) {
            $sections[] = PageDefaults::section($data['add_section_type']);
        }

        $sections = $this->mergeTileUploads($sections, $request->file('sections', []));
        $sections = $this->mergeDesignStudioUploads($sections, $request->file('sections', []));
        $sections = $this->mergeCorporateUploads($sections, $request->file('sections', []));
        $sections = $this->mergeMockupPageUploads($sections, $request->file('sections', []));
        $sections = $this->mergeDesignerMockups($sections, $request->file('sections', []));
        $sections = $this->sanitizeTileVideos($sections);
        $sections = $this->stripTileUploadFields($sections);
        $sections = $this->stripDesignStudioUploadFields($sections);
        $sections = $this->stripCorporateUploadFields($sections);
        $sections = $this->stripMockupPageUploadFields($sections);
        $sections = $this->stripMockupUploadFields($sections);

        $page->update([
            'title' => $data['title'] ?: $page->title,
            'content' => ['sections' => $sections],
        ]);

        return redirect()->route('admin.builder.edit', $page)
            ->with('success', 'Page updated.');
    }

    public function updateGlobals(Request $request)
    {
        $validator = validator($request->all(), [
            'header' => ['array'],
            'footer' => ['array'],
            'purchase_popups' => ['array'],
            'purchase_popups.items' => ['array'],
            'purchase_popups.items.*.name' => ['nullable', 'string', 'max:80'],
            'purchase_popups.items.*.product' => ['nullable', 'string', 'max:120'],
            'purchase_popups.items.*.time' => ['nullable', 'string', 'max:80'],
            'purchase_popups.items.*.image' => ['nullable', 'string', 'max:2048'],
            'purchase_popups.items.*.url' => ['nullable', 'string', 'max:2048'],
            'purchase_popups.items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'purchase_popups.enabled' => ['nullable'],
            'purchase_popups.interval' => ['nullable', 'numeric', 'min:4', 'max:60'],
        ]);
        $validator->excludeUnvalidatedArrayKeys = false;
        $data = $validator->validate();

        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());

        $headerPayload = $request->input('header', null);
        if (is_array($headerPayload)) {
            $header->update(['content' => $headerPayload]);
        }

        $footerPayload = $request->input('footer', null);
        if (is_array($footerPayload)) {
            $footer->update(['content' => $footerPayload]);
        }

        $purchasePayload = $request->input('purchase_popups', null);
        if (is_array($purchasePayload)) {
            $purchasePayload['enabled'] = !empty($purchasePayload['enabled']);
            $interval = $purchasePayload['interval'] ?? ($purchasePopups->content['interval'] ?? 12);
            $interval = is_numeric($interval) ? (int) $interval : 12;
            $purchasePayload['interval'] = max(4, min(60, $interval));

            $items = collect($purchasePayload['items'] ?? [])
                ->filter(fn ($item) => is_array($item))
                ->map(function ($item) {
                    $productId = $item['product_id'] ?? null;
                    $productId = is_numeric($productId) ? (int) $productId : null;
                    return [
                        'name' => trim((string) ($item['name'] ?? '')),
                        'product' => trim((string) ($item['product'] ?? '')),
                        'time' => trim((string) ($item['time'] ?? '')),
                        'image' => trim((string) ($item['image'] ?? '')),
                        'url' => trim((string) ($item['url'] ?? '')),
                        'product_id' => $productId,
                    ];
                })
                ->filter(fn ($item) => $item['name'] !== '' || $item['product'] !== '')
                ->values()
                ->all();
            $purchasePayload['items'] = $items;

            $purchasePopups->update(['content' => $purchasePayload]);
        }

        return redirect()->route('admin.builder.index')
            ->with('success', 'Global sections updated.');
    }

    private function ensureDefaults()
    {
        $home = Page::ensure('home', 'Home', PageDefaults::home());
        $shop = Page::ensure('shop', 'Shop', PageDefaults::shop());
        $checkout = Page::ensure('checkout', 'Checkout', PageDefaults::checkout());
        $embroidery = Page::ensure('embroidery', 'Embroidery', PageDefaults::embroidery());
        $mockup = Page::ensure('mockup', 'Mockup', PageDefaults::mockup());
        $corporate = Page::ensure('corporate', 'Corporate', PageDefaults::corporate());

        return Page::orderBy('title')->get();
    }

    private function mergeTileUploads(array $sections, array $sectionFiles): array
    {
        foreach ($sectionFiles as $sectionIndex => $sectionPayload) {
            $tileFiles = $sectionPayload['data']['tiles'] ?? [];
            foreach ($tileFiles as $tileIndex => $tilePayload) {
                $file = $tilePayload['background_image_file'] ?? null;
                if ($file && isset($sections[$sectionIndex]['data']['tiles'][$tileIndex])) {
                    $path = $file->store('page-builder/tiles', 'public');
                    $sections[$sectionIndex]['data']['tiles'][$tileIndex]['background_image'] = Storage::url($path);
                }

                $altFile = $tilePayload['background_image_alt_file'] ?? null;
                if ($altFile && isset($sections[$sectionIndex]['data']['tiles'][$tileIndex])) {
                    $path = $altFile->store('page-builder/tiles', 'public');
                    $sections[$sectionIndex]['data']['tiles'][$tileIndex]['background_image_alt'] = Storage::url($path);
                }

                $videoFile = $tilePayload['video_file'] ?? null;
                if ($videoFile && isset($sections[$sectionIndex]['data']['tiles'][$tileIndex])) {
                    $path = $videoFile->store('page-builder/tiles', 'public');
                    $sections[$sectionIndex]['data']['tiles'][$tileIndex]['video_url'] = Storage::url($path);
                }
            }
        }

        return $sections;
    }

    private function stripTileUploadFields(array $sections): array
    {
        foreach ($sections as $sectionIndex => $section) {
            if (!isset($section['data']['tiles'])) {
                continue;
            }

            foreach ($section['data']['tiles'] as $tileIndex => $tile) {
                if (array_key_exists('background_image_file', $tile)) {
                    unset($sections[$sectionIndex]['data']['tiles'][$tileIndex]['background_image_file']);
                }
                if (array_key_exists('background_image_alt_file', $tile)) {
                    unset($sections[$sectionIndex]['data']['tiles'][$tileIndex]['background_image_alt_file']);
                }
                if (array_key_exists('video_file', $tile)) {
                    unset($sections[$sectionIndex]['data']['tiles'][$tileIndex]['video_file']);
                }
            }
        }

        return $sections;
    }

    private function mergeDesignStudioUploads(array $sections, array $sectionFiles): array
    {
        foreach ($sectionFiles as $sectionIndex => $sectionPayload) {
            if (!isset($sections[$sectionIndex]) || ($sections[$sectionIndex]['type'] ?? '') !== 'design_studio') {
                continue;
            }

            $file = $sectionPayload['data']['background_image_file'] ?? null;
            if (!$file) {
                continue;
            }

            $path = $file->store('page-builder/design-studio', 'public');
            $sections[$sectionIndex]['data']['background_image'] = Storage::url($path);
        }

        return $sections;
    }

    private function stripDesignStudioUploadFields(array $sections): array
    {
        foreach ($sections as $sectionIndex => $section) {
            if (($section['type'] ?? '') !== 'design_studio') {
                continue;
            }

            if (array_key_exists('background_image_file', $section['data'] ?? [])) {
                unset($sections[$sectionIndex]['data']['background_image_file']);
            }
        }

        return $sections;
    }

    private function mergeMockupPageUploads(array $sections, array $sectionFiles): array
    {
        foreach ($sectionFiles as $sectionIndex => $sectionPayload) {
            if (!isset($sections[$sectionIndex]) || ($sections[$sectionIndex]['type'] ?? '') !== 'mockup_page') {
                continue;
            }

            $logoFile = $sectionPayload['data']['brand_logo_file'] ?? null;
            if ($logoFile) {
                $path = $logoFile->store('page-builder/mockup', 'public');
                $sections[$sectionIndex]['data']['brand_logo'] = Storage::url($path);
            }

            $templateFiles = $sectionPayload['data']['templates']['all_results'] ?? [];
            if (!is_array($templateFiles)) {
                continue;
            }

            if (!isset($sections[$sectionIndex]['data']['templates']) || !is_array($sections[$sectionIndex]['data']['templates'])) {
                $sections[$sectionIndex]['data']['templates'] = [];
            }
            if (!isset($sections[$sectionIndex]['data']['templates']['all_results']) || !is_array($sections[$sectionIndex]['data']['templates']['all_results'])) {
                $sections[$sectionIndex]['data']['templates']['all_results'] = [];
            }

            foreach ($templateFiles as $templateIndex => $templatePayload) {
                $file = $templatePayload['image_file'] ?? null;
                if (!$file) {
                    continue;
                }

                if (!isset($sections[$sectionIndex]['data']['templates']['all_results'][$templateIndex]) || !is_array($sections[$sectionIndex]['data']['templates']['all_results'][$templateIndex])) {
                    $sections[$sectionIndex]['data']['templates']['all_results'][$templateIndex] = [];
                }

                $path = $file->store('page-builder/mockup/templates', 'public');
                $sections[$sectionIndex]['data']['templates']['all_results'][$templateIndex]['image'] = Storage::url($path);
            }
        }

        return $sections;
    }

    private function stripMockupPageUploadFields(array $sections): array
    {
        foreach ($sections as $sectionIndex => $section) {
            if (($section['type'] ?? '') !== 'mockup_page') {
                continue;
            }

            if (array_key_exists('brand_logo_file', $section['data'] ?? [])) {
                unset($sections[$sectionIndex]['data']['brand_logo_file']);
            }

            $templateItems = $section['data']['templates']['all_results'] ?? [];
            if (!is_array($templateItems)) {
                continue;
            }

            foreach ($templateItems as $templateIndex => $template) {
                if (!is_array($template)) {
                    continue;
                }
                if (array_key_exists('image_file', $template)) {
                    unset($sections[$sectionIndex]['data']['templates']['all_results'][$templateIndex]['image_file']);
                }
            }
        }

        return $sections;
    }

    private function mergeCorporateUploads(array $sections, array $sectionFiles): array
    {
        foreach ($sectionFiles as $sectionIndex => $sectionPayload) {
            if (!isset($sections[$sectionIndex]) || ($sections[$sectionIndex]['type'] ?? '') !== 'corporate_page') {
                continue;
            }

            $file = $sectionPayload['data']['hero']['image_file'] ?? null;
            if (!$file) {
                continue;
            }

            $path = $file->store('page-builder/corporate', 'public');
            $sections[$sectionIndex]['data']['hero']['image'] = Storage::url($path);
        }

        return $sections;
    }

    private function stripCorporateUploadFields(array $sections): array
    {
        foreach ($sections as $sectionIndex => $section) {
            if (($section['type'] ?? '') !== 'corporate_page') {
                continue;
            }

            if (array_key_exists('image_file', $section['data']['hero'] ?? [])) {
                unset($sections[$sectionIndex]['data']['hero']['image_file']);
            }
        }

        return $sections;
    }

    private function mergeDesignerMockups(array $sections, array $sectionFiles): array
    {
        foreach ($sectionFiles as $sectionIndex => $sectionPayload) {
            $mockupFiles = $sectionPayload['data']['mockups'] ?? [];
            if (!is_array($mockupFiles)) {
                continue;
            }

            $existing = $sections[$sectionIndex]['data']['mockups'] ?? [];
            if (!is_array($existing)) {
                $existing = [];
            }
            $sections[$sectionIndex]['data']['mockups'] = $this->mergeMockupFilesRecursive($existing, $mockupFiles);
        }

        return $sections;
    }

    private function stripMockupUploadFields(array $sections): array
    {
        foreach ($sections as $sectionIndex => $section) {
            if (!isset($section['data']['mockups'])) {
                continue;
            }

            $mockups = $section['data']['mockups'];
            if (!is_array($mockups)) {
                continue;
            }
            $sections[$sectionIndex]['data']['mockups'] = $this->stripMockupUploadFieldsRecursive($mockups);
        }

        return $sections;
    }

    private function mergeMockupFilesRecursive(array $existing, array $fileTree): array
    {
        foreach ($fileTree as $key => $value) {
            if (is_array($value)) {
                $branch = $existing[$key] ?? [];
                if (!is_array($branch)) {
                    $branch = [];
                }
                $existing[$key] = $this->mergeMockupFilesRecursive($branch, $value);
                continue;
            }

            if (!$value) {
                continue;
            }

            $rawKey = (string) $key;
            $baseKey = str_ends_with($rawKey, '_file')
                ? substr($rawKey, 0, -5)
                : $rawKey;

            $path = $value->store('page-builder/mockups', 'public');
            $existing[$baseKey] = Storage::url($path);
        }

        return $existing;
    }

    private function stripMockupUploadFieldsRecursive(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (str_ends_with((string) $key, '_file')) {
                unset($payload[$key]);
                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->stripMockupUploadFieldsRecursive($value);
            }
        }

        return $payload;
    }

    private function sanitizeTileVideos(array $sections): array
    {
        foreach ($sections as $sectionIndex => $section) {
            if (!isset($section['data']['tiles'])) {
                continue;
            }

            foreach ($section['data']['tiles'] as $tileIndex => $tile) {
                if (!isset($tile['video_url'])) {
                    continue;
                }

                $url = (string) $tile['video_url'];
                if ($url === '') {
                    continue;
                }

                $lower = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
                if (!str_ends_with($lower, '.mp4') && !str_ends_with($lower, '.webm') && !str_ends_with($lower, '.ogg')) {
                    $sections[$sectionIndex]['data']['tiles'][$tileIndex]['video_url'] = null;
                }
            }
        }

        return $sections;
    }
}
