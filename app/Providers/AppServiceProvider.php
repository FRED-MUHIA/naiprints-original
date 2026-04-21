<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('partials.site-header', function ($view): void {
            $headerSearchEntries = Cache::remember('site-header-search-entries:v1', now()->addMinutes(20), function () {
                return Product::query()
                    ->select(['id', 'name', 'slug', 'status', 'category_id'])
                    ->with(['category:id,name'])
                    ->where(function ($query) {
                        $query
                            ->where('status', 'active')
                            ->orWhereNull('status');
                    })
                    ->orderBy('name')
                    ->get()
                    ->map(function (Product $product) {
                        $title = trim((string) $product->name);
                        if ($title === '') {
                            return null;
                        }

                        return [
                            'title' => $title,
                            'subtitle' => trim((string) ($product->category?->name ?? 'Product')),
                            'url' => route('products.show', ['product' => $product->slug ?: $product->getKey()]),
                            'type' => 'Product',
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            });

            $view->with('headerSearchEntries', $headerSearchEntries);
        });
    }
}
