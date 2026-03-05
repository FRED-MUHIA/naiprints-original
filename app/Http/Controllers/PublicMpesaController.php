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
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
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
        $phone = $service->normalizePhone($data['phone']);
        $amount = (float) $invoice->balance;
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
