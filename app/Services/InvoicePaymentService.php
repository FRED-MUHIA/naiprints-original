<?php

namespace App\Services;

use App\Mail\ReceiptMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Support\DocumentSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoicePaymentService
{
    public function __construct(
        private MailDispatchService $mailDispatch,
        private AffiliateService $affiliateService
    )
    {
    }

    public function record(Invoice $invoice, array $data): Payment
    {
        $receipt = null;

        $payment = DB::transaction(function () use ($invoice, $data, &$receipt) {
            $payment = $invoice->payments()->create($data);

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();

            $receiptSequence = DocumentSequence::next(Receipt::class);
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

        $this->affiliateService->createCommissionForPaidInvoice($invoice->fresh(['affiliate']), $payment);

        if ($receipt) {
            $this->mailDispatch->sendToRecipients(
                [
                    (string) $invoice->client?->email,
                    (string) config('mail.support.address', config('mail.from.address')),
                ],
                fn () => new ReceiptMail($receipt),
                [
                    'event' => 'invoice.receipt_mail',
                    'invoice_id' => $invoice->id,
                    'receipt_id' => $receipt->id,
                ]
            );
        }

        return $payment;
    }
}
