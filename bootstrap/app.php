<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (
    extension_loaded('pdo_mysql') &&
    defined('PDO::MYSQL_ATTR_SSL_CA') &&
    !class_exists(\Pdo\Mysql::class, false)
) {
    class_alias(\App\Support\PdoMysqlCompat::class, \Pdo\Mysql::class);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\CaptureAffiliateReferral::class);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'admin.can' => \App\Http\Middleware\AdminAuthorize::class,
            'api.key' => \App\Http\Middleware\RequireApiKey::class,
            'affiliate.auth' => \App\Http\Middleware\AffiliateAuth::class,
            'mpesa.callback' => \App\Http\Middleware\ValidateMpesaCallbackSource::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
