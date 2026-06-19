<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class CanonicalSeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $targetHost = $this->canonicalHost($request->getHost());
        $targetUri = $this->canonicalUri($request->getRequestUri());

        if ($targetHost !== null || $targetUri !== null) {
            $host = $targetHost ?? $request->getHost();
            $uri = $targetUri ?? $request->getRequestUri();

            return new RedirectResponse($this->scheme($request) . '://' . $host . $this->port($request) . $uri, 301);
        }

        return $next($request);
    }

    private function canonicalHost(string $host): ?string
    {
        if (str_starts_with(strtolower($host), 'www.')) {
            return substr($host, 4);
        }

        return null;
    }

    private function canonicalUri(string $uri): ?string
    {
        if (!preg_match('#^/public(?=/|\?|$)#i', $uri)) {
            return null;
        }

        $cleanUri = preg_replace('#^/public(?=/|\?|$)#i', '', $uri, 1) ?: '/';

        if (str_starts_with($cleanUri, '?')) {
            return '/' . $cleanUri;
        }

        return $cleanUri;
    }

    private function scheme(Request $request): string
    {
        return $request->headers->get('X-Forwarded-Proto') === 'https' || $request->isSecure()
            ? 'https'
            : $request->getScheme();
    }

    private function port(Request $request): string
    {
        $port = $request->getPort();

        if (($this->scheme($request) === 'https' && $port === 443) || ($this->scheme($request) === 'http' && $port === 80)) {
            return '';
        }

        return ':' . $port;
    }
}
