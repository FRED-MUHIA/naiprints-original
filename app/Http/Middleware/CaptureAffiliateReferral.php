<?php

namespace App\Http\Middleware;

use App\Services\AffiliateService;
use Closure;
use Illuminate\Http\Request;

class CaptureAffiliateReferral
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && $request->query('ref')) {
            app(AffiliateService::class)->captureClick($request);
        }

        return $next($request);
    }
}
