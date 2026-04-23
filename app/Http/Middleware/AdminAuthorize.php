<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthorize
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $currentRole = strtolower((string) $request->session()->get('admin_role', 'admin'));
        $allowedRoles = collect($roles)
            ->map(fn ($role) => strtolower(trim($role)))
            ->filter()
            ->values()
            ->all();

        if (empty($allowedRoles) || in_array($currentRole, $allowedRoles, true)) {
            return $next($request);
        }

        if ($currentRole === 'editor') {
            return redirect()->route('admin.products.index')
                ->withErrors(['access' => 'Your account can only access product management.']);
        }

        abort(403);
    }
}

