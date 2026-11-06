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
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $queryString = (string) (parse_url($uri, PHP_URL_QUERY) ?? '');
        $query = [];
        parse_str($queryString, $query);

        $changed = false;
        if (preg_match('#^/public(?=/|$)#i', $path)) {
            $path = preg_replace('#^/public(?=/|$)#i', '', $path, 1) ?: '/';
            $changed = true;
        }

        if (array_key_exists('add-to-wishlist', $query)) {
            unset($query['add-to-wishlist']);
            $changed = true;
        }

        if (!$changed) {
            return null;
        }

        $cleanUri = $path . ($query ? '?' . http_build_query($query) : '');

        return $cleanUri !== '' ? $cleanUri : '/';
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
