<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceSettingController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\MpesaController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\MpesaCallbackController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\PublicMpesaController;
use App\Http\Controllers\PublicReceiptController;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GlobalSection;
use App\Models\Product;
use App\Models\Size;
use App\Models\Tag;
use App\Models\Page;
use App\Support\PageDefaults;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\CorporateQuoteController;
use App\Http\Controllers\CheckoutController;

if (!function_exists('resolvePurchasePopupProducts')) {
    function resolvePurchasePopupProducts(array $popupContent): array
    {
        $items = $popupContent['items'] ?? [];
        $productIds = collect($items)
            ->map(fn ($item) => is_array($item) ? ($item['product_id'] ?? null) : null)
            ->filter()
            ->unique()
            ->values();
        $productNames = collect($items)
            ->map(function ($item) {
                if (!is_array($item)) {
                    return null;
                }
                $name = trim((string) ($item['product'] ?? ''));
                return $name !== '' ? Str::of($name)->lower()->trim()->toString() : null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty() && $productNames->isEmpty()) {
            return [];
        }

        $products = Product::with(['galleryImages', 'alternateImages', 'images'])
            ->when(!$productIds->isEmpty(), fn ($query) => $query->whereIn('id', $productIds))
            ->when(!$productNames->isEmpty(), function ($query) use ($productNames) {
                $query->orWhereIn('name', $productNames);
            })
            ->get();

        return $products->mapWithKeys(function (Product $product) {
            $image = $product->image_url;
            if (!$image) {
                $fallback = $product->galleryImages->first()
                    ?? $product->alternateImages->first()
                    ?? $product->images->first();
                if ($fallback && $fallback->image_path) {
                    $image = Storage::url($fallback->image_path);
                }
            }

            $payload = [
                'name' => $product->name,
                'image' => $image ?? '',
                'url' => route('products.show', $product),
            ];

            $nameKey = Str::of($product->name)->lower()->trim()->toString();

            return [
                $product->id => $payload,
                "name:{$nameKey}" => $payload,
            ];
        })->all();
    }
}

Route::get('/', function () {
    $page = Page::ensure('home', 'Home', PageDefaults::home());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $categories = [
        [
            'title' => 'Uncategorized',
            'items' => 3,
            'gradient' => 'linear-gradient(135deg, #f4d2c6, #f8e5d9)',
        ],
        [
            'title' => 'T-shirt',
            'items' => 16,
            'gradient' => 'linear-gradient(135deg, #e5e7eb, #ffffff)',
        ],
        [
            'title' => 'Hoodie',
            'items' => 9,
            'gradient' => 'linear-gradient(135deg, #fbcfe8, #fdf2f8)',
        ],
        [
            'title' => 'Polo T-shirt',
            'items' => 1,
            'gradient' => 'linear-gradient(135deg, #f1d4b2, #fde68a)',
        ],
        [
            'title' => 'Pillows',
            'items' => 2,
            'gradient' => 'linear-gradient(135deg, #a7f3d0, #d1fae5)',
        ],
        [
            'title' => 'Kids',
            'items' => 8,
            'gradient' => 'linear-gradient(135deg, #e0f2fe, #f0f9ff)',
        ],
        [
            'title' => 'Corporate',
            'items' => 6,
            'gradient' => 'linear-gradient(135deg, #dbeafe, #eff6ff)',
        ],
        [
            'title' => 'Gifts',
            'items' => 5,
            'gradient' => 'linear-gradient(135deg, #fde68a, #fef3c7)',
        ],
    ];

    $products = [
        [
            'title' => 'Pray Minimal Tee',
            'price' => 'KSh 1,450',
            'tag' => 'Best seller',
            'swatch' => 'from-[#0f172a] via-[#111827] to-[#f59e0b]',
        ],
        [
            'title' => 'Own Path Hoodie',
            'price' => 'KSh 2,950',
            'tag' => 'New drop',
            'swatch' => 'from-[#0b1026] via-[#1f2937] to-[#e11d2e]',
        ],
        [
            'title' => 'Onyx Sports Set',
            'price' => 'KSh 3,200',
            'tag' => 'Limited',
            'swatch' => 'from-[#111827] via-[#0f172a] to-[#22c55e]',
        ],
        [
            'title' => 'Electric Pop Tee',
            'price' => 'KSh 1,750',
            'tag' => 'Vibrant',
            'swatch' => 'from-[#111827] via-[#312e81] to-[#f97316]',
        ],
        [
            'title' => 'Corporate Polo Pack',
            'price' => 'KSh 4,800',
            'tag' => 'Bulk',
            'swatch' => 'from-[#0f172a] via-[#1e293b] to-[#38bdf8]',
        ],
        [
            'title' => 'Kids Joy Set',
            'price' => 'KSh 1,300',
            'tag' => 'Soft touch',
            'swatch' => 'from-[#0f172a] via-[#334155] to-[#eab308]',
        ],
        [
            'title' => 'Couple Match Pack',
            'price' => 'KSh 2,600',
            'tag' => '2 pieces',
            'swatch' => 'from-[#111827] via-[#1f2937] to-[#fb7185]',
        ],
        [
            'title' => 'Birthday Crew Kit',
            'price' => 'KSh 3,400',
            'tag' => 'Event',
            'swatch' => 'from-[#0b1026] via-[#111827] to-[#a855f7]',
        ],
    ];

    $featuredProducts = Product::with(['category', 'brand', 'sizes', 'tags', 'alternateImages', 'galleryImages'])
        ->orderByDesc('created_at')
        ->take(8)
        ->get();

    return view('home', [
        'categories' => $categories,
        'products' => $products,
        'featuredProducts' => $featuredProducts,
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
});

Route::get('/embroidery', function () {
    $page = Page::ensure('embroidery', 'Embroidery', PageDefaults::embroidery());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('embroidery', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('embroidery');

Route::get('/shop', function () {
    $page = Page::ensure('shop', 'Shop', PageDefaults::shop());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $categories = Category::orderBy('name')->get();
    $minPrice = request('min_price');
    $maxPrice = request('max_price');
    $minPrice = is_numeric($minPrice) ? (float) $minPrice : null;
    $maxPrice = is_numeric($maxPrice) ? (float) $maxPrice : null;

    $productsQuery = Product::with(['category', 'brand'])
        ->when($minPrice !== null, fn ($query) => $query->where('price', '>=', $minPrice))
        ->when($maxPrice !== null, fn ($query) => $query->where('price', '<=', $maxPrice))
        ->orderByDesc('created_at');

    $products = $productsQuery->take(12)->get();

    $fallbackProducts = collect([
        ['name' => 'Classic Tee', 'price' => 1290, 'rating' => 4.9, 'sold' => 3523],
        ['name' => 'Logo Hoodie', 'price' => 2990, 'rating' => 4.8, 'sold' => 2781],
        ['name' => 'Team Jersey', 'price' => 2490, 'rating' => 4.7, 'sold' => 1640],
        ['name' => 'Corporate Polo', 'price' => 1890, 'rating' => 4.6, 'sold' => 980],
        ['name' => 'Kids Tee', 'price' => 990, 'rating' => 4.8, 'sold' => 1210],
        ['name' => 'Event Hoodie', 'price' => 3290, 'rating' => 4.9, 'sold' => 870],
        ['name' => 'Athletic Set', 'price' => 3590, 'rating' => 4.7, 'sold' => 640],
        ['name' => 'Gift Bundle', 'price' => 2690, 'rating' => 4.6, 'sold' => 430],
    ]);
    if ($minPrice !== null || $maxPrice !== null) {
        $fallbackProducts = $fallbackProducts->filter(function ($item) use ($minPrice, $maxPrice) {
            $price = (float) ($item['price'] ?? 0);
            if ($minPrice !== null && $price < $minPrice) {
                return false;
            }
            if ($maxPrice !== null && $price > $maxPrice) {
                return false;
            }
            return true;
        })->values();
    }

    return view('shop', [
        'categories' => $categories,
        'products' => $products,
        'fallbackProducts' => $fallbackProducts,
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice,
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('shop');

Route::get('/checkout', function () {
    $page = Page::ensure('checkout', 'Checkout', PageDefaults::checkout());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('checkout', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('checkout');

Route::post('/checkout/submit', [CheckoutController::class, 'submit'])
    ->name('checkout.submit');

Route::get('/corporate', function () {
    $page = Page::ensure('corporate', 'Corporate', PageDefaults::corporate());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('corporate', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('corporate');

Route::post('/newsletter', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email:rfc,dns'],
    ]);

    return back()->with('newsletter_subscribed', true);
})->name('newsletter.subscribe');

Route::post('/corporate/quote', [CorporateQuoteController::class, 'store'])
    ->name('corporate.quote');

Route::get('/mockup', function () {
    $page = Page::ensure('mockup', 'Mockup', PageDefaults::mockup());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('mockup', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('mockup');

Route::get('/products/{product}', function (Product $product) {
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $product->load(['category', 'brand', 'alternateImages', 'galleryImages', 'attributes', 'sizes', 'tags']);
    $relatedProducts = Product::with(['category', 'alternateImages', 'galleryImages'])
        ->where('id', '!=', $product->id)
        ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
        ->orderByDesc('created_at')
        ->take(4)
        ->get();

    if ($relatedProducts->count() < 4) {
        $moreProducts = Product::with(['category', 'alternateImages', 'galleryImages'])
            ->where('id', '!=', $product->id)
            ->whereNotIn('id', $relatedProducts->pluck('id'))
            ->orderByDesc('created_at')
            ->take(4 - $relatedProducts->count())
            ->get();
        $relatedProducts = $relatedProducts->concat($moreProducts);
    }

    return view('product', [
        'product' => $product,
        'relatedProducts' => $relatedProducts,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('products.show');

Route::get('/invoice/view/{invoice:public_id}', [PublicInvoiceController::class, 'show'])
    ->name('invoice.public');
Route::get('/invoice/view/{invoice:public_id}/pdf', [PublicInvoiceController::class, 'pdf'])
    ->name('invoice.public.pdf');
Route::get('/invoice/view/{invoice:public_id}/status', [PublicInvoiceController::class, 'status'])
    ->name('invoice.payment.status');
Route::get('/invoice/verify/{invoice:public_id}', [PublicInvoiceController::class, 'verify'])
    ->name('invoice.verify')
    ->middleware('signed');
Route::post('/invoice/view/{invoice:public_id}/mpesa', [PublicMpesaController::class, 'pay'])
    ->name('invoice.public.mpesa');
Route::get('/receipt/view/{receipt:public_id}', [PublicReceiptController::class, 'show'])
    ->name('receipt.public');
Route::get('/receipt/view/{receipt:public_id}/pdf', [PublicReceiptController::class, 'pdf'])
    ->name('receipt.public.pdf');

Route::post('/mpesa/stk/callback', [MpesaCallbackController::class, 'stkCallback'])
    ->name('mpesa.stk.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/mpesa/c2b/validation', [MpesaCallbackController::class, 'c2bValidation'])
    ->name('mpesa.c2b.validation')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/mpesa/c2b/confirmation', [MpesaCallbackController::class, 'c2bConfirmation'])
    ->name('mpesa.c2b.confirmation')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $expectedUser = config('admin.username');
    $expectedHash = config('admin.password_hash');

    if (
        $credentials['username'] === $expectedUser &&
        Hash::check($credentials['password'], $expectedHash)
    ) {
        $request->session()->put('admin_authenticated', true);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    return back()->withErrors(['username' => 'Invalid credentials.'])->withInput();
})->name('admin.login.submit');

Route::post('/admin/logout', function (Request $request) {
    $request->session()->forget('admin_authenticated');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
})->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', function () {
        $categoryId = request('category');

        $products = Product::with(['category', 'brand'])
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $featuredProduct = $products->first();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.dashboard', compact('products', 'featuredProduct', 'categories', 'brands'));
    })->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::resource('tags', TagController::class)->except(['show']);
    Route::resource('attributes', AttributeController::class)->except(['show']);
    Route::resource('sizes', SizeController::class)->except(['show']);
    Route::resource('media', MediaAssetController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('invoices/trash', [InvoiceController::class, 'trash'])->name('invoices.trash');
    Route::post('invoices/{invoice}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
    Route::delete('invoices/{invoice}/force', [InvoiceController::class, 'forceDelete'])->name('invoices.force');
    Route::get('invoices/settings', [InvoiceSettingController::class, 'edit'])->name('invoices.settings');
    Route::post('invoices/settings', [InvoiceSettingController::class, 'update'])->name('invoices.settings.update');
    Route::post('invoices/settings/test-email', [InvoiceSettingController::class, 'testEmail'])->name('invoices.settings.test');
    Route::post('invoices/settings/mpesa/register', [MpesaController::class, 'registerC2b'])->name('invoices.mpesa.register');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('invoices.payments.store');
    Route::post('invoices/{invoice}/mpesa/stk', [MpesaController::class, 'stkPush'])->name('invoices.mpesa.stk');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoices.send');
    Route::resource('invoices', InvoiceController::class);
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');

    Route::get('builder', [PageBuilderController::class, 'index'])->name('builder.index');
    Route::post('builder/globals', [PageBuilderController::class, 'updateGlobals'])->name('builder.globals');
    Route::get('builder/{page}', [PageBuilderController::class, 'edit'])->name('builder.edit');
    Route::put('builder/{page}', [PageBuilderController::class, 'update'])->name('builder.update');
});
