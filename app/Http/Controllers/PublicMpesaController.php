<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;

class PublicMpesaController extends Controller
{
    public function pay(Request $request, Invoice $invoice)
    {
        $maxAmount = max(1, (float) $invoice->balance);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $maxAmount],
        ]);

        if ($invoice->balance < 1) {
            return back()->withErrors(['phone' => 'Invoice balance is already cleared.']);
        }

        $settings = InvoiceSetting::first() ?? InvoiceSetting::create(InvoiceSetting::defaults());
        $service = new MpesaService($settings);

        if (!$service->isConfigured()) {
            return back()->withErrors(['phone' => 'M-Pesa is not configured yet.']);
        }

        $config = $settings->mpesa_config ?? [];
        try {
            $phone = method_exists($service, 'validatePhone')
                ? $service->validatePhone($data['phone'])
                : (method_exists($service, 'normalizePhone')
                    ? $service->normalizePhone($data['phone'])
                    : (function (string $raw): string {
                        $digits = preg_replace('/\D+/', '', $raw) ?? '';
                        if ($digits === '') {
                            return '';
                        }
                        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
                            return '254' . substr($digits, 1);
                        }
                        if (str_starts_with($digits, '7') && strlen($digits) === 9) {
                            return '254' . $digits;
                        }

                        return $digits;
                    })((string) $data['phone']));
        } catch (\Throwable $exception) {
            return back()->withErrors(['phone' => $exception->getMessage()]);
        }

        $amount = method_exists($service, 'normalizeAmount')
            ? $service->normalizeAmount((float) $data['amount'])
            : max(1, (int) round((float) $data['amount']));

        $rawCallbackUrl = (string) ($config['callback_url'] ?? secure_url('/mpesa/stk/callback'));
        $callbackUrl = method_exists($service, 'sanitizeCallbackUrl')
            ? $service->sanitizeCallbackUrl($rawCallbackUrl)
            : trim($rawCallbackUrl);
        $accountReference = $invoice->number;
        $description = 'Invoice payment';
        $hasRecentPending = MpesaTransaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('phone', $phone)
            ->where('type', 'stk')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if ($hasRecentPending) {
            return back()->withErrors([
                'phone' => 'An STK prompt was already sent in the last 2 minutes. Complete it on your phone, then retry if needed.',
            ]);
        }

        try {
            $response = $service->stkPush([
                'Amount' => $amount,
                'PartyA' => $phone,
                'PhoneNumber' => $phone,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $accountReference,
                'TransactionDesc' => $description,
            ]);
        } catch (\Throwable $exception) {
            logger()->error('Public STK push failed', [
                'invoice_id' => $invoice->id,
                'phone' => $phone,
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'error' => $exception->getMessage(),
            ]);
            return back()->withErrors(['phone' => 'STK push failed: ' . $exception->getMessage()]);
        }

        MpesaTransaction::create([
            'invoice_id' => $invoice->id,
            'type' => 'stk',
            'phone' => $phone,
            'amount' => $amount,
            'reference' => $accountReference,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'status' => 'pending',
            'result_desc' => $response['ResponseDescription'] ?? null,
            'raw_request' => [
                'request' => [
                    'amount' => $amount,
                    'phone' => $phone,
                    'callback_url' => $callbackUrl,
                    'account_reference' => $accountReference,
                ],
                'response' => $response,
            ],
        ]);

        return back()->with('success', 'STK push sent. Enter your M-Pesa PIN on your phone to complete payment.');
    }
}
