<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MpesaController extends Controller
{
    public function testCredentials(Request $request)
    {
        $settings = InvoiceSetting::first() ?? InvoiceSetting::create(InvoiceSetting::defaults());
        $service = new MpesaService($settings);
        $config = $service->config();

        if (!$service->isConfigured()) {
            return back()->withErrors(['mpesa' => 'Please save your M-Pesa environment, consumer key, consumer secret, and shortcode first.']);
        }

        if (empty($config['passkey'])) {
            return back()->withErrors(['mpesa' => 'Please add your M-Pesa passkey.']);
        }

        $environment = $config['environment'] ?? 'sandbox';
        Cache::forget('mpesa_token_sandbox');
        Cache::forget('mpesa_token_live');

        try {
            $token = $service->accessToken();
            $tokenPreview = substr((string) $token, 0, 10);

            return back()->with('success', "M-Pesa credentials test passed for {$environment}. Access token received ({$tokenPreview}...).");
        } catch (\Throwable $exception) {
            logger()->error('M-Pesa credentials test failed', [
                'environment' => $environment,
                'shortcode' => $config['shortcode'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['mpesa' => 'Credentials test failed: ' . $exception->getMessage()]);
        }
    }

    public function registerC2b(Request $request)
    {
        $settings = InvoiceSetting::first() ?? InvoiceSetting::create(InvoiceSetting::defaults());
        $service = new MpesaService($settings);

        if (!$service->isConfigured()) {
            return back()->withErrors(['mpesa' => 'Please save your M-Pesa keys and shortcode first.']);
        }

        $config = $settings->mpesa_config ?? [];
        $confirmationUrl = $config['confirmation_url'] ?? route('mpesa.c2b.confirmation');
        $validationUrl = $config['validation_url'] ?? route('mpesa.c2b.validation');
        $responseType = $config['response_type'] ?? 'Completed';

        try {
            $service->registerC2B($confirmationUrl, $validationUrl, $responseType);
        } catch (\Throwable $exception) {
            return back()->withErrors(['mpesa' => 'C2B registration failed: ' . $exception->getMessage()]);
        }

        return back()->with('success', 'C2B callback URLs registered successfully.');
    }

    public function stkPush(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $settings = InvoiceSetting::first() ?? InvoiceSetting::create(InvoiceSetting::defaults());
        $service = new MpesaService($settings);

        if (!$service->isConfigured()) {
            return back()->withErrors(['mpesa' => 'Please save your M-Pesa keys and shortcode first.']);
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
            return back()->withErrors(['mpesa' => $exception->getMessage()]);
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
                'mpesa' => 'An STK prompt was already sent in the last 2 minutes. Complete it on the phone, then retry if needed.',
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
            logger()->error('Admin STK push failed', [
                'invoice_id' => $invoice->id,
                'phone' => $phone,
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'error' => $exception->getMessage(),
            ]);
            return back()->withErrors(['mpesa' => 'STK push failed: ' . $exception->getMessage()]);
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

        return back()->with('success', 'STK push sent to the customer.');
    }
}
