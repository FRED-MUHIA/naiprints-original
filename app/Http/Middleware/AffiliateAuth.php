<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AffiliateAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Schema::hasTable('affiliates')) {
            $request->session()->forget('affiliate_id');

            return redirect()->route('affiliate.login')
                ->withErrors(['setup' => 'Affiliate login is not ready yet. Run php artisan migrate to create the affiliate tables.']);
        }

        $affiliateId = $request->session()->get('affiliate_id');
        $affiliate = $affiliateId ? Affiliate::find($affiliateId) : null;

        if (!$affiliate || !$affiliate->isApproved()) {
            $request->session()->forget('affiliate_id');

            return redirect()->route('affiliate.login');
        }

        return $next($request);
    }
}
