<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliateSetting;
use App\Services\AffiliateService;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $affiliates = Affiliate::withCount(['clicks', 'invoices', 'commissions'])
            ->withSum('commissions as pending_commission', 'amount')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $settings = AffiliateSetting::current();
        $pendingPayouts = AffiliatePayout::with('affiliate')->where('status', AffiliatePayout::STATUS_PENDING)->latest()->take(8)->get();
        $pendingCommissions = AffiliateCommission::with(['affiliate', 'invoice'])->where('status', AffiliateCommission::STATUS_PENDING)->latest()->take(10)->get();

        return view('admin.affiliates.index', compact('affiliates', 'settings', 'status', 'pendingPayouts', 'pendingCommissions'));
    }

    public function show(Affiliate $affiliate)
    {
        $affiliate->loadCount(['clicks', 'invoices'])
            ->load(['commissions.invoice.client', 'payouts']);

        return view('admin.affiliates.show', compact('affiliate'));
    }

    public function approve(Affiliate $affiliate, AffiliateService $affiliateService)
    {
        $affiliate->forceFill([
            'status' => Affiliate::STATUS_APPROVED,
            'approved_at' => now(),
            'rejected_at' => null,
        ])->save();
        $affiliate->ensureReferralCode();
        $affiliateService->notify(
            $affiliate,
            'Affiliate account approved',
            'Your affiliate account is active. You can now log in at ' . route('affiliate.login') . ' and copy your referral link from your dashboard. Your referral link is ' . $affiliate->referral_url
        );

        return back()->with('success', 'Affiliate approved.');
    }

    public function reject(Affiliate $affiliate, AffiliateService $affiliateService)
    {
        $affiliate->forceFill([
            'status' => Affiliate::STATUS_REJECTED,
            'rejected_at' => now(),
        ])->save();
        $affiliateService->notify($affiliate, 'Affiliate account rejected', 'Your affiliate application was not approved at this time.');

        return back()->with('success', 'Affiliate rejected.');
    }

    public function update(Request $request, Affiliate $affiliate)
    {
        $data = $request->validate([
            'commission_type' => ['nullable', 'in:percentage,fixed'],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:120'],
            'payment_details' => ['required', 'string', 'max:2000'],
        ]);

        $affiliate->update($data);

        return back()->with('success', 'Affiliate updated.');
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'commission_type' => ['required', 'in:percentage,fixed'],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'minimum_payout_amount' => ['required', 'numeric', 'min:0'],
        ]);

        AffiliateSetting::current()->update($data);

        return back()->with('success', 'Affiliate settings saved.');
    }

    public function updateCommission(Request $request, AffiliateCommission $commission)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,paid'],
        ]);

        $timestamps = [
            'approved_at' => $data['status'] === AffiliateCommission::STATUS_APPROVED ? now() : $commission->approved_at,
            'rejected_at' => $data['status'] === AffiliateCommission::STATUS_REJECTED ? now() : $commission->rejected_at,
            'paid_at' => $data['status'] === AffiliateCommission::STATUS_PAID ? now() : $commission->paid_at,
        ];

        $commission->update(['status' => $data['status'], ...$timestamps]);

        return back()->with('success', 'Commission updated.');
    }

    public function updatePayout(Request $request, AffiliatePayout $payout, AffiliateService $affiliateService)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,paid'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payout->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $payout->notes,
            'approved_at' => $data['status'] === AffiliatePayout::STATUS_APPROVED ? now() : $payout->approved_at,
            'rejected_at' => $data['status'] === AffiliatePayout::STATUS_REJECTED ? now() : $payout->rejected_at,
            'paid_at' => $data['status'] === AffiliatePayout::STATUS_PAID ? now() : $payout->paid_at,
        ]);

        if ($data['status'] === AffiliatePayout::STATUS_PAID) {
            $remaining = (float) $payout->amount;
            $commissions = $payout->affiliate->commissions()
                ->where('status', AffiliateCommission::STATUS_APPROVED)
                ->oldest()
                ->get();

            foreach ($commissions as $commission) {
                if ($remaining <= 0) {
                    break;
                }

                if ((float) $commission->amount > $remaining + 0.01) {
                    continue;
                }

                $commission->update([
                    'status' => AffiliateCommission::STATUS_PAID,
                    'paid_at' => now(),
                ]);
                $remaining -= (float) $commission->amount;
            }
        }

        $affiliateService->notify($payout->affiliate, 'Payout ' . $data['status'], 'Your payout request for KES ' . number_format((float) $payout->amount, 2) . ' was marked ' . $data['status'] . '.');

        return back()->with('success', 'Payout updated.');
    }

    public function export()
    {
        $filename = 'affiliate-report-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Status', 'Referral Code', 'Clicks', 'Paid Orders', 'Pending', 'Approved', 'Paid']);

            Affiliate::withCount(['clicks', 'invoices'])
                ->with('commissions')
                ->chunk(200, function ($affiliates) use ($handle) {
                    foreach ($affiliates as $affiliate) {
                        fputcsv($handle, [
                            $affiliate->full_name,
                            $affiliate->email,
                            $affiliate->status,
                            $affiliate->referral_code,
                            $affiliate->clicks_count,
                            $affiliate->invoices_count,
                            $affiliate->commissions->where('status', 'pending')->sum('amount'),
                            $affiliate->commissions->where('status', 'approved')->sum('amount'),
                            $affiliate->commissions->where('status', 'paid')->sum('amount'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename);
    }
}
