<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('admin_authenticated')) {
            return redirect()->route('admin.login');
        }

        $role = strtolower((string) $request->session()->get('admin_role', 'admin'));
        $request->session()->put('admin_role', $role);

        $userId = $request->session()->get('admin_user_id');
        if ($userId) {
            $user = User::query()->find($userId);
            if (!$user || !$user->canAccessAdminPanel()) {
                $request->session()->forget(['admin_authenticated', 'admin_role', 'admin_user_id', 'admin_name']);
                return redirect()->route('admin.login');
            }

            $actualRole = strtolower((string) ($user->role ?: 'editor'));
            if ($role !== $actualRole) {
                $request->session()->put('admin_role', $actualRole);
            }
        }

        return $next($request);
    }
}
