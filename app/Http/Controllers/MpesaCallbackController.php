<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MpesaTransaction;
use App\Mail\OrderPlaced;
use App\Services\InvoicePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class MpesaCallbackController extends Controller
{
    public function stkCallback(Request $request, InvoicePaymentService $payments)
    {
        $payload = $request->all();
        $callback = data_get($payload, 'Body.stkCallback');

        if (!$callback) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback payload'], 400);
        }

        $checkoutId = $callback['CheckoutRequestID'] ?? null;
        $resultCode = (string) ($callback['ResultCode'] ?? '');
        $resultDesc = $callback['ResultDesc'] ?? null;

        $transaction = $checkoutId
            ? MpesaTransaction::firstOrNew(['checkout_request_id' => $checkoutId])
            : new MpesaTransaction();

        $transaction->fill([
            'type' => $transaction->type ?: 'stk',
            'status' => $resultCode === '0' ? 'success' : 'failed',
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'raw_callback' => $payload,
        ]);

        $metadata = collect($callback['CallbackMetadata']['Item'] ?? [])
            ->mapWithKeys(function ($item) {
                return [$item['Name'] ?? '' => $item['Value'] ?? null];
            });

        $amount = (float) ($metadata->get('Amount') ?? 0);
        $receipt = $metadata->get('MpesaReceiptNumber');
        $phone = $metadata->get('PhoneNumber');
        $transactionDate = $metadata->get('TransactionDate');

        $transaction->amount = $amount ?: $transaction->amount;
        $transaction->receipt_number = $receipt ?: $transaction->receipt_number;
        $transaction->phone = $phone ?: $transaction->phone;
        if ($transactionDate) {
            try {
                $transaction->occurred_at = Carbon::createFromFormat('YmdHis', (string) $transactionDate);
            } catch (\Throwable $exception) {
                // Keep existing occurred_at if parsing fails.
            }
        }

        $transaction->save();

        if ($resultCode === '0' && !$transaction->payment_id) {
            if ($transaction->type === 'checkout') {
                $raw = $transaction->raw_request ?? [];
                $order = $raw['order'] ?? null;
                $support = $raw['support'] ?? [];
                $alreadySent = (bool) ($raw['order_email_sent'] ?? false);

                if ($order && !$alreadySent) {
                    $customerEmail = data_get($order, 'customer.email');
                    if ($customerEmail) {
                        Mail::to($customerEmail)->send(new OrderPlaced($order, $support));
                    }
                    $supportEmail = $support['email'] ?? config('mail.from.address');
                    if ($supportEmail) {
                        Mail::to($supportEmail)->send(new OrderPlaced($order, $support));
                    }
                    $raw['order_email_sent'] = true;
                    $transaction->raw_request = $raw;
                    $transaction->save();
                }

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            }

            $invoice = $transaction->invoice ?: $this->resolveInvoice($transaction->reference);
            if ($invoice && $amount > 0) {
                $payment = $payments->record($invoice, [
                    'amount' => $amount,
                    'method' => 'M-Pesa',
                    'payment_date' => $transaction->occurred_at?->toDateString() ?? now()->toDateString(),
                    'reference' => $receipt,
                    'note' => 'M-Pesa STK push',
                ]);

                $transaction->payment_id = $payment->id;
                $transaction->invoice_id = $invoice->id;
                $transaction->save();
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function c2bValidation(Request $request)
    {
        $reference = $request->input('BillRefNumber');

        if ($reference && !$this->resolveInvoice($reference)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid account reference']);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function c2bConfirmation(Request $request, InvoicePaymentService $payments)
    {
        $payload = $request->all();

        $reference = $payload['BillRefNumber'] ?? null;
        $amount = (float) ($payload['TransAmount'] ?? 0);
        $receipt = $payload['TransID'] ?? null;
        $phone = $payload['MSISDN'] ?? null;
        $transactionTime = $payload['TransTime'] ?? null;
        $occurredAt = now();
        if ($transactionTime) {
            try {
                $occurredAt = Carbon::createFromFormat('YmdHis', (string) $transactionTime);
            } catch (\Throwable $exception) {
                $occurredAt = now();
            }
        }

        $invoice = $this->resolveInvoice($reference);

        $transaction = MpesaTransaction::firstOrNew([
            'receipt_number' => $receipt,
        ]);

        $transaction->fill([
            'invoice_id' => $invoice?->id,
            'type' => 'c2b',
            'phone' => $phone,
            'amount' => $amount,
            'reference' => $reference,
            'status' => $invoice ? 'success' : 'failed',
            'result_code' => $invoice ? '0' : '1',
            'result_desc' => $invoice ? 'Accepted' : 'Invoice not found',
            'raw_callback' => $payload,
            'occurred_at' => $occurredAt,
        ]);

        $transaction->save();

        if ($invoice && $amount > 0 && !$transaction->payment_id) {
            $payment = $payments->record($invoice, [
                'amount' => $amount,
                'method' => 'M-Pesa',
                'payment_date' => $occurredAt->toDateString(),
                'reference' => $receipt,
                'note' => 'M-Pesa C2B payment',
            ]);

            $transaction->payment_id = $payment->id;
            $transaction->save();
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    private function resolveInvoice(?string $reference): ?Invoice
    {
        if (!$reference) {
            return null;
        }

        return Invoice::where('number', $reference)
            ->orWhere('public_id', $reference)
            ->first();
    }
}
