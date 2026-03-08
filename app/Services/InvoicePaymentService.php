<?php

namespace App\Services;

use App\Mail\ReceiptMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvoicePaymentService
{
    public function record(Invoice $invoice, array $data): Payment
    {
        $receipt = null;

        $payment = DB::transaction(function () use ($invoice, $data, &$receipt) {
            $payment = $invoice->payments()->create($data);

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();

            $receiptSequence = (int) Receipt::max('sequence') + 1;
            $receipt = Receipt::create([
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'sequence' => $receiptSequence,
                'number' => 'RCPT-' . str_pad((string) $receiptSequence, 4, '0', STR_PAD_LEFT),
                'public_id' => (string) Str::uuid(),
                'amount' => $payment->amount,
                'balance_after' => $invoice->balance,
                'method' => $payment->method,
                'payment_date' => $payment->payment_date,
            ]);

            return $payment;
        });

        $payment->setRelation('receipt', $receipt);
        $invoice->loadMissing('client');

        if ($receipt && $invoice->client?->email) {
            try {
                Mail::to($invoice->client->email)->queue(new ReceiptMail($receipt));
            } catch (\Throwable $exception) {
                logger()->error('Receipt email failed', [
                    'invoice_id' => $invoice->id,
                    'receipt_id' => $receipt->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $payment;
    }
}
