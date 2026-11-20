<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Page;
use App\Models\Size;
use App\Services\HomeClothingRailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeClothingRailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Cache::flush();
    }

    public function test_it_uses_real_active_stocked_products_with_images_and_prioritizes_apparel(): void
    {
        $teeCategory = Category::create(['name' => 'T-shirt', 'slug' => 't-shirt']);
        $mugCategory = Category::create(['name' => 'Mugs', 'slug' => 'mugs']);
        $tee = $this->makeProduct($teeCategory, 'Nairobi Print Tee', 'nairobi-print-tee', 1500, 8);
        $mug = $this->makeProduct($mugCategory, 'Ceramic Mug', 'ceramic-mug', 700, 20);
        $this->makeProduct($teeCategory, 'Draft Tee', 'draft-tee', 12, 50, 'draft');
        $this->makeProduct($teeCategory, 'Out of stock Tee', 'out-of-stock-tee', 12, 0);
        Product::create([
            'category_id' => $teeCategory->id,
            'name' => 'Missing image Tee',
            'slug' => 'missing-image-tee',
            'price' => 1000,
            'stock' => 5,
            'status' => 'active',
        ]);

        $items = app(HomeClothingRailService::class)->products([], 2);

        $this->assertSame([$tee->id, $mug->id], $items->pluck('id')->all());
        $this->assertSame('KSh ' . number_format(1500), 'KSh ' . $items->first()['price']);
        $this->assertSame(route('products.show', ['product' => $tee->slug]), $items->first()['url']);
        $this->assertSame('/storage/products/nairobi-print-tee.jpg', $items->first()['image']);
        $this->assertSame('hook_only', $items->first()['hanger_style']);
        $this->assertSame(12, $items->first()['image_overlap']);
    }

    public function test_it_applies_category_filter_and_current_sale_price(): void
    {
        $teeCategory = Category::create(['name' => 'T-shirt', 'slug' => 't-shirt']);
        $hoodieCategory = Category::create(['name' => 'Hoodies', 'slug' => 'hoodies']);
        $tee = $this->makeProduct($teeCategory, 'Sale Tee', 'sale-tee', 1500, 4);
        $tee->update(['discount_type' => 'percent', 'discount_value' => 50, 'color_options' => ['Black', 'White']]);
        $size = Size::create(['name' => 'Large', 'slug' => 'large']);
        $tee->sizes()->sync([$size->id => ['price' => 1500, 'stock' => 4]]);
        $this->makeProduct($hoodieCategory, 'Hoodie', 'hoodie', 2500, 7);

        $items = app(HomeClothingRailService::class)->products([$teeCategory->id], 10);

        $this->assertCount(1, $items);
        $this->assertSame('750', $items->first()['price']);
        $this->assertTrue($items->first()['is_on_sale']);
        $this->assertSame(['Large'], $items->first()['sizes']);
        $this->assertSame(['Black', 'White'], $items->first()['colors']);
    }

    public function test_homepage_renders_rail_products_from_the_store_catalog(): void
    {
        $category = Category::create(['name' => 'Hoodies', 'slug' => 'hoodies']);
        $this->makeProduct($category, 'Real Store Hoodie', 'real-store-hoodie', 2500, 3);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-clothing-rail', false)
            ->assertSee('Real Store Hoodie')
            ->assertDontSee('Need to Find a Gift?')
            ->assertSee('KSh 2,500')
            ->assertSee(route('products.show', ['product' => 'real-store-hoodie']));
    }

    public function test_homepage_defaults_the_rail_appearance_for_saved_settings_without_that_key(): void
    {
        Page::create([
            'slug' => 'home',
            'title' => 'Home',
            'content' => ['sections' => [
                ['type' => 'best_sellers', 'data' => ['product_category_id' => null]],
            ]],
        ]);
        $category = Category::create(['name' => 'T-shirt', 'slug' => 't-shirt']);
        $this->makeProduct($category, 'Legacy Setting Tee', 'legacy-setting-tee', 1500, 4);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-appearance="showroom"', false)
            ->assertSee('Legacy Setting Tee');
    }

    public function test_homepage_uses_the_selected_rail_category_and_custom_background(): void
    {
        $hoodies = Category::create(['name' => 'Hoodies', 'slug' => 'hoodies']);
        $tees = Category::create(['name' => 'T-shirt', 'slug' => 't-shirt']);
        $this->makeProduct($hoodies, 'Rail Hoodie Only', 'rail-hoodie-only', 2500, 3);
        $this->makeProduct($tees, 'Not in the selected rail category', 'not-in-selected-rail-category', 1500, 3);
        Page::create([
            'slug' => 'home',
            'title' => 'Home',
            'content' => ['sections' => [[
                'type' => 'best_sellers',
                'data' => ['clothing_rail' => [
                    'enabled' => true,
                    'category_id' => $hoodies->id,
                    'category_ids' => [$hoodies->id],
                    'appearance' => 'custom',
                    'background_color' => '#243447',
                ]],
            ]]],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rail Hoodie Only')
            ->assertDontSee('data-name="Not in the selected rail category"', false)
            ->assertSee('data-appearance="custom"', false)
            ->assertSee('style="--rail-bg: #243447"', false);
    }

    public function test_rail_replaces_the_gift_finder_nested_in_how_it_works(): void
    {
        $category = Category::create(['name' => 'Hoodies', 'slug' => 'hoodies']);
        $this->makeProduct($category, 'Nested Gift Slot Hoodie', 'nested-gift-slot-hoodie', 2500, 3);
        Page::create([
            'slug' => 'home',
            'title' => 'Home',
            'content' => ['sections' => [
                ['type' => 'best_sellers', 'data' => ['clothing_rail' => ['enabled' => true]]],
                ['type' => 'how_it_works', 'data' => [
                    'title' => 'How it works after the rail',
                    'steps' => [],
                    'gift_finder' => ['title' => 'Need to Find a Gift Art?'],
                ]],
            ]],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['data-clothing-rail', 'How it works after the rail'], false)
            ->assertSee('Nested Gift Slot Hoodie')
            ->assertDontSee('Need to Find a Gift Art?');
    }

    public function test_product_updates_invalidate_the_cached_rail_catalog(): void
    {
        $category = Category::create(['name' => 'Jerseys', 'slug' => 'jerseys']);
        $product = $this->makeProduct($category, 'Club Jersey', 'club-jersey', 1800, 5);
        $service = app(HomeClothingRailService::class);

        $this->assertSame('1,800', $service->products()->first()['price']);

        $product->update(['price' => 2100]);
        $this->assertSame('2,100', $service->products()->first()['price']);

        $product->update(['stock' => 0]);
        $this->assertCount(0, $service->products());
    }

    public function test_rail_uses_side_view_at_rest_and_supplies_front_and_back_views(): void
    {
        $category = Category::create(['name' => 'T-shirt', 'slug' => 't-shirt']);
        $product = $this->makeProduct($category, 'Three View Tee', 'three-view-tee', 1800, 5);
        $product->update(['rail_hanger_style' => 'full', 'rail_image_overlap' => 18]);
        Storage::disk('public')->put('products/rail/three-view-side.jpg', 'side');
        Storage::disk('public')->put('products/rail/three-view-back.jpg', 'back');
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/rail/three-view-side.jpg',
            'position' => 0,
            'type' => 'gallery',
            'view' => 'side',
        ]);
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/rail/three-view-back.jpg',
            'position' => 1,
            'type' => 'gallery',
            'view' => 'back',
        ]);

        $item = app(HomeClothingRailService::class)->products()->first();

        $this->assertSame('/storage/products/three-view-tee.jpg', $item['front_image']);
        $this->assertSame('/storage/products/rail/three-view-side.jpg', $item['side_image']);
        $this->assertSame('/storage/products/rail/three-view-back.jpg', $item['back_image']);
        $this->assertTrue($item['has_back_image']);
        $this->assertSame('full', $item['hanger_style']);
        $this->assertSame(18, $item['image_overlap']);
    }

    private function makeProduct(Category $category, string $name, string $slug, float $price, int $stock, string $status = 'active'): Product
    {
        $path = 'products/' . $slug . '.jpg';
        Storage::disk('public')->put($path, 'image');

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'price' => $price,
            'stock' => $stock,
            'status' => $status,
            'image_path' => $path,
        ]);
    }
}
