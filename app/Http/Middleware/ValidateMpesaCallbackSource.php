<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class ValidateMpesaCallbackSource
{
    public function handle(Request $request, Closure $next)
    {
        $allowedIps = array_filter(array_map(
            'trim',
            explode(',', (string) config('mpesa.callback_allowed_ips', ''))
        ));
        $expectedSecret = trim((string) config('mpesa.callback_secret', ''));

        if ($allowedIps !== [] && !IpUtils::checkIp($request->ip(), $allowedIps)) {
            logger()->warning('Rejected M-Pesa callback from unexpected IP.', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(403, 'Invalid callback source.');
        }

        if ($expectedSecret !== '') {
            $providedSecret = trim((string) (
                $request->header('X-Mpesa-Callback-Secret')
                ?: $request->query('token', '')
            ));

            if ($providedSecret === '' || !hash_equals($expectedSecret, $providedSecret)) {
                logger()->warning('Rejected M-Pesa callback with invalid callback secret.', [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                ]);

                abort(403, 'Invalid callback secret.');
            }
        }

        if ($allowedIps === [] && $expectedSecret === '' && app()->environment('production')) {
            logger()->warning('M-Pesa callback source validation is not configured.');
        }

        return $next($request);
    }
}
