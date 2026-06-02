<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MpesaTransaction;
use App\Mail\OrderPlaced;
use App\Services\InvoicePaymentService;
use App\Services\MailDispatchService;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MpesaCallbackController extends Controller
{
    public function stkCallback(
        Request $request,
        InvoicePaymentService $payments,
        MailDispatchService $mailDispatch,
        MpesaService $mpesa
    )
    {
        $payload = $request->all();
        $callback = data_get($payload, 'Body.stkCallback');

        if (!is_array($callback)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback payload'], 400);
        }

        $checkoutId = trim((string) ($callback['CheckoutRequestID'] ?? ''));
        if ($checkoutId === '') {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing checkout request ID'], 400);
        }

        $resultCode = (string) ($callback['ResultCode'] ?? '');
        $resultDesc = $callback['ResultDesc'] ?? null;

        $transaction = MpesaTransaction::checkout($checkoutId)->latest('id')->first();
        if (!$transaction) {
            logger()->warning('Ignored M-Pesa STK callback for unknown checkout request.', [
                'checkout_request_id' => $checkoutId,
                'merchant_request_id' => $callback['MerchantRequestID'] ?? null,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $transaction->fill([
            'type' => $transaction->type ?: 'stk',
            'status' => $resultCode === '0' ? MpesaTransaction::STATUS_PENDING : MpesaTransaction::STATUS_FAILED,
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

        if ($resultCode === '0') {
            if (!$receipt || $amount <= 0) {
                $transaction->status = MpesaTransaction::STATUS_FAILED;
                $transaction->result_desc = 'Rejected successful callback with missing receipt or amount.';
                $transaction->save();

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            if (!$this->callbackMatchesInitiatedTransaction($transaction, $amount, $phone ? (string) $phone : null)) {
                logger()->warning('Rejected M-Pesa STK callback that did not match initiated transaction.', [
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $checkoutId,
                    'expected_amount' => $transaction->amount,
                    'callback_amount' => $amount,
                    'expected_phone' => $transaction->phone,
                    'callback_phone' => $phone,
                ]);

                $transaction->status = MpesaTransaction::STATUS_FAILED;
                $transaction->result_desc = 'Rejected callback because amount or phone did not match the initiated transaction.';
                $transaction->save();

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            if ($this->receiptAlreadyRecorded((string) $receipt, (int) $transaction->id)) {
                logger()->warning('Rejected duplicate M-Pesa receipt.', [
                    'transaction_id' => $transaction->id,
                    'receipt_number' => $receipt,
                ]);

                $transaction->status = MpesaTransaction::STATUS_FAILED;
                $transaction->result_desc = 'Rejected duplicate M-Pesa receipt.';
                $transaction->save();

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            try {
                $verification = $mpesa->validateTransaction($checkoutId);
                $transaction->refresh();
                if (($verification['result_code'] ?? null) !== '0') {
                    $transaction->status = MpesaTransaction::STATUS_FAILED;
                    $transaction->result_desc = 'Rejected callback because M-Pesa query did not confirm success.';
                    $transaction->save();

                    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
                }
            } catch (\Throwable $exception) {
                logger()->warning('M-Pesa STK callback received but verification query failed.', [
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $checkoutId,
                    'error' => $exception->getMessage(),
                ]);

                $transaction->status = MpesaTransaction::STATUS_PENDING;
                $transaction->result_desc = 'Callback received; awaiting M-Pesa verification.';
                $transaction->save();

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            $rawRequest = is_array($transaction->raw_request) ? $transaction->raw_request : [];
            $isCheckoutOrder = ($transaction->type === 'checkout')
                || (($rawRequest['source'] ?? null) === 'checkout');

            if ($isCheckoutOrder) {
                $this->sendMissingOrderEmails($transaction, $mailDispatch, $checkoutId);
            }

            if (!$transaction->payment_id) {
                $invoice = $transaction->invoice;
                if (!$invoice) {
                    $invoiceId = data_get($rawRequest, 'invoice_id');
                    if (is_numeric($invoiceId)) {
                        $invoice = Invoice::find((int) $invoiceId);
                    }
                }

                $invoice = $invoice ?: $this->resolveInvoice($transaction->reference);
                if ($invoice && $amount > 0) {
                    $invoice->loadMissing('payments');
                    if ($amount > ((float) $invoice->balance + 0.01)) {
                        $transaction->status = MpesaTransaction::STATUS_FAILED;
                        $transaction->result_desc = 'Rejected callback because payment exceeds invoice balance.';
                        $transaction->save();

                        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
                    }

                    $payment = $payments->record($invoice, [
                        'amount' => $amount,
                        'method' => 'M-Pesa',
                        'payment_date' => $transaction->occurred_at?->toDateString() ?? now()->toDateString(),
                        'reference' => $receipt,
                        'note' => 'M-Pesa STK push',
                    ]);

                    $transaction->payment_id = $payment->id;
                    $transaction->invoice_id = $invoice->id;
                    $transaction->status = MpesaTransaction::STATUS_SUCCESS;
                    $transaction->save();
                }
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    public function c2bValidation(Request $request)
    {
        $reference = $request->input('BillRefNumber');
        $amount = (float) $request->input('TransAmount', 0);

        $invoice = $this->resolveInvoice($reference);
        if ($reference && !$invoice) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid account reference']);
        }

        if ($invoice && $amount > 0 && $amount > ((float) $invoice->balance + 0.01)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Payment exceeds invoice balance']);
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
            'receipt_number' => $receipt ?: 'missing-' . sha1(json_encode($payload) ?: (string) now()),
        ]);

        $isValid = $invoice && $amount > 0 && $receipt && !$this->receiptAlreadyRecorded((string) $receipt, (int) ($transaction->id ?? 0));
        if ($isValid && $amount > ((float) $invoice->balance + 0.01)) {
            $isValid = false;
        }

        $transaction->fill([
            'invoice_id' => $invoice?->id,
            'type' => 'c2b',
            'phone' => $phone,
            'amount' => $amount,
            'reference' => $reference,
            'status' => $isValid ? MpesaTransaction::STATUS_SUCCESS : MpesaTransaction::STATUS_FAILED,
            'result_code' => $isValid ? '0' : '1',
            'result_desc' => $isValid ? 'Accepted' : 'Rejected: invalid invoice, duplicate receipt, missing amount, or amount exceeds balance',
            'raw_callback' => $payload,
            'occurred_at' => $occurredAt,
        ]);

        $transaction->save();

        if ($isValid && !$transaction->payment_id) {
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

    private function callbackMatchesInitiatedTransaction(MpesaTransaction $transaction, float $amount, ?string $phone): bool
    {
        $expectedAmount = (float) $transaction->amount;
        if ($expectedAmount > 0 && abs($expectedAmount - $amount) > 0.01) {
            return false;
        }

        $expectedPhone = preg_replace('/\D+/', '', (string) $transaction->phone) ?: '';
        $callbackPhone = preg_replace('/\D+/', '', (string) $phone) ?: '';

        return $expectedPhone === '' || $callbackPhone === '' || $expectedPhone === $callbackPhone;
    }

    private function receiptAlreadyRecorded(string $receipt, int $ignoreTransactionId = 0): bool
    {
        $receipt = trim($receipt);
        if ($receipt === '') {
            return false;
        }

        return MpesaTransaction::query()
            ->where('receipt_number', $receipt)
            ->whereNotNull('payment_id')
            ->when($ignoreTransactionId > 0, fn ($query) => $query->where('id', '!=', $ignoreTransactionId))
            ->exists();
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

    private function sendMissingOrderEmails(
        MpesaTransaction $transaction,
        MailDispatchService $mailDispatch,
        ?string $checkoutRequestId = null
    ): void {
        $raw = is_array($transaction->raw_request) ? $transaction->raw_request : [];
        $order = $raw['order'] ?? null;
        if (!is_array($order) || empty($order)) {
            return;
        }

        $support = is_array($raw['support'] ?? null) ? $raw['support'] : [];
        $targetRecipients = $mailDispatch->uniqueRecipients([
            (string) data_get($order, 'customer.email', ''),
            (string) data_get($support, 'email', config('mail.support.address', config('mail.from.address'))),
        ]);
        if (empty($targetRecipients)) {
            return;
        }

        $sentRecipients = $mailDispatch->uniqueRecipients($raw['order_email_sent_to'] ?? []);
        $pendingRecipients = array_values(array_diff($targetRecipients, $sentRecipients));

        if (!empty($pendingRecipients)) {
            $delivery = $mailDispatch->sendToRecipients(
                $pendingRecipients,
                fn () => new OrderPlaced($order, $support),
                [
                    'event' => 'mpesa.checkout.order_placed_mail',
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $checkoutRequestId,
                ]
            );
            $sentRecipients = $mailDispatch->uniqueRecipients([
                ...$sentRecipients,
                ...$delivery['sent'],
            ]);
            $failedRecipients = $mailDispatch->uniqueRecipients([
                ...($raw['order_email_failed_to'] ?? []),
                ...$delivery['failed'],
            ]);
            $raw['order_email_failed_to'] = array_values(array_diff($failedRecipients, $sentRecipients));
        }

        $raw['order_email_sent_to'] = $sentRecipients;
        $raw['order_email_sent'] = empty(array_diff($targetRecipients, $sentRecipients));
        $raw['order_email_last_attempt_at'] = now()->toIso8601String();

        $transaction->raw_request = $raw;
        $transaction->save();
    }
}
