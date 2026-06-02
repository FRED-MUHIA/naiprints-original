<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $expectedKey = trim((string) config('services.api.key', ''));
        if ($expectedKey === '') {
            abort(403, 'API access is not configured.');
        }

        $providedKey = trim((string) $request->header('X-API-Key', ''));
        if ($providedKey === '') {
            $providedKey = trim((string) $request->bearerToken());
        }

        if ($providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
            abort(401, 'Invalid API key.');
        }

        return $next($request);
    }
}
