<?php

namespace App\Services;

use App\Mail\AffiliateNotificationMail;
use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateCommission;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;

class AffiliateService
{
    public const COOKIE_CODE = 'nai_affiliate_ref';
    public const COOKIE_CLICK = 'nai_affiliate_click';
    public const COOKIE_DAYS = 30;

    public function __construct(private MailDispatchService $mailDispatch)
    {
    }

    public function captureClick(Request $request): void
    {
        if (!$this->tablesReady()) {
            return;
        }

        $code = trim((string) $request->query('ref', ''));
        if ($code === '') {
            return;
        }

        $affiliate = Affiliate::query()
            ->where('referral_code', $code)
            ->where('status', Affiliate::STATUS_APPROVED)
            ->first();

        if (!$affiliate) {
            return;
        }

        $click = AffiliateClick::create([
            'affiliate_id' => $affiliate->id,
            'referral_code' => $affiliate->referral_code,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'referral_source' => substr((string) $request->headers->get('referer'), 0, 2000),
            'landing_url' => substr($request->fullUrl(), 0, 2000),
        ]);

        Cookie::queue(self::COOKIE_CODE, $affiliate->referral_code, self::COOKIE_DAYS * 24 * 60);
        Cookie::queue(self::COOKIE_CLICK, (string) $click->id, self::COOKIE_DAYS * 24 * 60);
    }

    public function resolveReferral(Request $request, ?string $customerEmail = null, ?string $customerPhone = null): ?array
    {
        if (!$this->tablesReady()) {
            return null;
        }

        $code = trim((string) $request->cookie(self::COOKIE_CODE, ''));
        if ($code === '') {
            return null;
        }

        $affiliate = Affiliate::query()
            ->where('referral_code', $code)
            ->where('status', Affiliate::STATUS_APPROVED)
            ->first();

        if (!$affiliate || $this->isOwnPurchase($affiliate, $customerEmail, $customerPhone)) {
            return null;
        }

        $clickId = $request->cookie(self::COOKIE_CLICK);
        $click = is_numeric($clickId)
            ? AffiliateClick::query()->where('affiliate_id', $affiliate->id)->find((int) $clickId)
            : null;

        return [
            'affiliate_id' => $affiliate->id,
            'affiliate_click_id' => $click?->id,
            'affiliate_code' => $affiliate->referral_code,
        ];
    }

    public function createCommissionForPaidInvoice(Invoice $invoice, ?Payment $payment = null): ?AffiliateCommission
    {
        if (!$this->tablesReady()) {
            return null;
        }

        $invoice->loadMissing('affiliate', 'items');

        if (!$invoice->affiliate || $invoice->status !== 'paid') {
            return null;
        }

        $affiliate = $invoice->affiliate;
        $type = $affiliate->commissionType();
        $value = $affiliate->commissionValue();
        $created = collect();

        foreach ($invoice->items as $item) {
            if ((float) $item->total <= 0) {
                continue;
            }

            $existing = AffiliateCommission::query()
                ->where('invoice_item_id', $item->id)
                ->first();
            if ($existing) {
                continue;
            }

            $lineTotal = (float) $item->total;
            $amount = $type === 'fixed'
                ? $value
                : round($lineTotal * ($value / 100), 2);

            $created->push(AffiliateCommission::create([
                'affiliate_id' => $affiliate->id,
                'invoice_id' => $invoice->id,
                'invoice_item_id' => $item->id,
                'product_id' => $item->product_id,
                'payment_id' => $payment?->id,
                'order_total' => $lineTotal,
                'commission_type' => $type,
                'commission_value' => $value,
                'amount' => max(0, $amount),
                'status' => AffiliateCommission::STATUS_PENDING,
            ]));
        }

        if ($created->isEmpty()) {
            return AffiliateCommission::query()->where('invoice_id', $invoice->id)->first();
        }

        $this->notify(
            $affiliate,
            'New product commission earned',
            'New pending product-based commission totaling KES ' . number_format((float) $created->sum('amount'), 2) . ' was created for invoice ' . $invoice->number . '.'
        );

        return $created->first();
    }

    public function rejectCommissionForInvoice(Invoice $invoice, string $reason): void
    {
        if (!Schema::hasTable('affiliate_commissions')) {
            return;
        }

        $commission = AffiliateCommission::query()->where('invoice_id', $invoice->id)->first();
        if (!$commission || $commission->status === AffiliateCommission::STATUS_PAID) {
            return;
        }

        $commission->update([
            'status' => AffiliateCommission::STATUS_REJECTED,
            'notes' => $reason,
            'rejected_at' => now(),
        ]);
    }

    public function notify(Affiliate $affiliate, string $subject, string $message): void
    {
        $this->mailDispatch->sendToRecipients(
            [(string) $affiliate->email],
            fn () => new AffiliateNotificationMail($subject, $message, $affiliate),
            ['event' => 'affiliate.notification', 'affiliate_id' => $affiliate->id]
        );
    }

    private function isOwnPurchase(Affiliate $affiliate, ?string $customerEmail, ?string $customerPhone): bool
    {
        $email = strtolower(trim((string) $customerEmail));
        if ($email !== '' && $email === strtolower((string) $affiliate->email)) {
            return true;
        }

        $normalizePhone = static fn ($value) => preg_replace('/\D+/', '', (string) $value) ?: '';
        $buyerPhone = $normalizePhone($customerPhone);
        $affiliatePhone = $normalizePhone($affiliate->phone);

        return $buyerPhone !== '' && $affiliatePhone !== '' && $buyerPhone === $affiliatePhone;
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('affiliates')
            && Schema::hasTable('affiliate_clicks')
            && Schema::hasTable('affiliate_commissions')
            && Schema::hasTable('invoice_items')
            && Schema::hasColumn('affiliate_commissions', 'invoice_item_id')
            && Schema::hasColumn('invoice_items', 'product_id')
            && Schema::hasTable('affiliate_settings');
    }
}
