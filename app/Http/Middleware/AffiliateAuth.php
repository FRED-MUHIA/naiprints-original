<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;
use Illuminate\Http\Request;

class AffiliateAuth
{
    public function handle(Request $request, Closure $next)
    {
        $affiliateId = $request->session()->get('affiliate_id');
        $affiliate = $affiliateId ? Affiliate::find($affiliateId) : null;

        if (!$affiliate || !$affiliate->isApproved()) {
            $request->session()->forget('affiliate_id');

            return redirect()->route('affiliate.login');
        }

        return $next($request);
    }
}
