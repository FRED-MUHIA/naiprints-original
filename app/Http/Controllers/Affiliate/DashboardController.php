<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliateSetting;
use App\Models\Product;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $affiliate = $this->affiliate($request);
        $affiliate->ensureReferralCode();

        $clicks = $affiliate->clicks()->count();
        $orders = $affiliate->invoices()->where('status', 'paid')->count();
        $conversionRate = $clicks > 0 ? round(($orders / $clicks) * 100, 2) : 0;

        $commissionSums = $affiliate->commissions()
            ->selectRaw("status, SUM(amount) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $referredOrders = $affiliate->invoices()
            ->with(['client', 'affiliateCommissions.product'])
            ->latest()
            ->paginate(10, ['*'], 'orders_page');

        $payouts = $affiliate->payouts()->latest()->paginate(8, ['*'], 'payouts_page');
        $approvedBalance = (float) ($commissionSums[AffiliateCommission::STATUS_APPROVED] ?? 0);
        $reservedPayouts = (float) $affiliate->payouts()
            ->whereIn('status', [AffiliatePayout::STATUS_PENDING, AffiliatePayout::STATUS_APPROVED])
            ->sum('amount');
        $availableBalance = max(0, $approvedBalance - $reservedPayouts);
        $minimumPayout = (float) AffiliateSetting::current()->minimum_payout_amount;
        $referralProducts = Schema::hasTable('products')
            ? Product::query()->where('status', 'active')->orderBy('name')->take(24)->get(['id', 'name', 'slug', 'price'])
            : collect();

        return view('affiliate.dashboard', compact(
            'affiliate',
            'clicks',
            'orders',
            'conversionRate',
            'commissionSums',
            'referredOrders',
            'payouts',
            'availableBalance',
            'minimumPayout',
            'referralProducts'
        ));
    }

    public function requestPayout(Request $request, AffiliateService $affiliateService)
    {
        $affiliate = $this->affiliate($request);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'max:120'],
            'payment_details' => ['required', 'string', 'max:2000'],
        ]);

        $approvedBalance = (float) $affiliate->commissions()
            ->where('status', AffiliateCommission::STATUS_APPROVED)
            ->sum('amount');
        $pendingPayouts = (float) $affiliate->payouts()
            ->whereIn('status', [AffiliatePayout::STATUS_PENDING, AffiliatePayout::STATUS_APPROVED])
            ->sum('amount');
        $available = max(0, $approvedBalance - $pendingPayouts);
        $minimum = (float) AffiliateSetting::current()->minimum_payout_amount;

        if ((float) $data['amount'] > $available) {
            return back()->withErrors(['amount' => 'Requested amount is above your available approved commission.'])->withInput();
        }

        if ((float) $data['amount'] < $minimum) {
            return back()->withErrors(['amount' => 'Minimum payout amount is KES ' . number_format($minimum, 2) . '.'])->withInput();
        }

        AffiliatePayout::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_details' => $data['payment_details'],
        ]);

        $affiliateService->notify($affiliate, 'Payout request submitted', 'Your payout request for KES ' . number_format((float) $data['amount'], 2) . ' has been submitted.');

        return back()->with('success', 'Payout request submitted.');
    }

    private function affiliate(Request $request): Affiliate
    {
        return Affiliate::findOrFail((int) $request->session()->get('affiliate_id'));
    }
}
