<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (!app()->bound('request')) {
            return;
        }

        $request = request();
        $basePath = rtrim((string) $request->getBasePath(), '/');
        if ($basePath === '') {
            $uriPath = parse_url((string) $request->getRequestUri(), PHP_URL_PATH) ?? '';
            if ($uriPath === '/public' || str_starts_with($uriPath, '/public/')) {
                $basePath = '/public';
            }
        }
        if ($basePath === '') {
            return;
        }

        $root = $request->getSchemeAndHttpHost().$basePath;
        URL::forceRootUrl($root);
        config([
            'app.url' => $root,
            'app.asset_url' => $root,
        ]);
    }
}
