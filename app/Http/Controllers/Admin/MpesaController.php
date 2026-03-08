<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
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
        $phone = $service->normalizePhone($data['phone']);
        $amount = (float) $data['amount'];
        $callbackUrl = $config['callback_url'] ?? route('mpesa.stk.callback');
        $accountReference = $invoice->number;
        $description = 'Invoice ' . $invoice->number . ' payment';

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
