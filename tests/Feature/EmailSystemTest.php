<?php

namespace Tests\Feature;

use App\Mail\InvoiceMail;
use App\Mail\OrderPlaced;
use App\Mail\ReceiptMail;
use App\Models\Client;
use App\Models\GlobalSection;
use App\Models\Invoice;
use App\Models\MpesaTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_sends_invoice_and_order_confirmation_to_customer_and_support(): void
    {
        Mail::fake();

        GlobalSection::create([
            'key' => 'footer',
            'content' => [
                'brand' => 'Nai Prints',
                'columns' => [
                    [
                        'title' => 'Contact',
                        'links' => [
                            ['label' => 'Call Us', 'url' => 'tel:+254700000000'],
                            ['label' => 'Email Us', 'url' => 'mailto:sales@naiprints.co.ke'],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->post(route('checkout.submit'), [
            'name' => 'Jane Doe',
            'email' => 'customer@example.com',
            'phone' => '0700000000',
            'address' => 'Kimathi Street',
            'city' => 'Nairobi',
            'notes' => 'Deliver after 4 PM',
            'payment' => 'delivery',
            'cart_items' => json_encode([
                [
                    'id' => '1',
                    'name' => 'Classic Tee',
                    'price' => 1500,
                    'qty' => 2,
                    'color' => 'Black',
                    'size' => 'L',
                ],
            ], JSON_THROW_ON_ERROR),
            'cart_subtotal' => 3000,
            'cart_total' => 3000,
        ]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);

        $response->assertRedirect(route('invoice.public', $invoice->public_id));
        Mail::assertSent(InvoiceMail::class, 2);
        Mail::assertSent(OrderPlaced::class, 2);
        Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo('customer@example.com'));
        Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo('sales@naiprints.co.ke'));
        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('customer@example.com'));
        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('sales@naiprints.co.ke'));
    }

    public function test_stk_success_sends_missing_order_mail_and_records_payment_and_receipt_mail(): void
    {
        Mail::fake();
        config()->set('mail.support.address', 'ops@naiprints.co.ke');

        $invoice = $this->createInvoice('customer@example.com');

        $transaction = MpesaTransaction::create([
            'invoice_id' => $invoice->id,
            'type' => 'stk',
            'phone' => '254700000000',
            'amount' => 3000,
            'reference' => $invoice->number,
            'checkout_request_id' => 'ws_CO_12345',
            'status' => 'pending',
            'raw_request' => [
                'source' => 'checkout',
                'invoice_id' => $invoice->id,
                'order' => [
                    'number' => $invoice->number,
                    'invoice_url' => route('invoice.public', $invoice->public_id),
                    'customer' => [
                        'name' => 'Jane Doe',
                        'email' => 'customer@example.com',
                    ],
                    'shipping' => [
                        'address' => 'Kimathi Street',
                        'city' => 'Nairobi',
                    ],
                    'items' => [
                        ['name' => 'Classic Tee', 'price' => 1500, 'qty' => 2],
                    ],
                    'subtotal' => 3000,
                    'total' => 3000,
                ],
                'support' => [
                    'brand' => 'Nai Prints',
                    'email' => 'mailto:sales@naiprints.co.ke',
                    'phone' => '0700 000 000',
                ],
                'order_email_sent_to' => ['customer@example.com'],
                'order_email_sent' => false,
            ],
        ]);

        $payload = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => '12345',
                    'CheckoutRequestID' => $transaction->checkout_request_id,
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 3000],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'RCP12345'],
                            ['Name' => 'PhoneNumber', 'Value' => 254700000000],
                            ['Name' => 'TransactionDate', 'Value' => now()->format('YmdHis')],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('mpesa.stk.callback'), $payload);

        $response->assertOk()
            ->assertJson([
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
            ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'reference' => 'RCP12345',
            'method' => 'M-Pesa',
        ]);
        $this->assertDatabaseCount('receipts', 1);

        Mail::assertSent(OrderPlaced::class, 1);
        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('sales@naiprints.co.ke'));
        Mail::assertNotSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('customer@example.com'));

        Mail::assertSent(ReceiptMail::class, fn (ReceiptMail $mail) => $mail->hasTo('customer@example.com'));
        Mail::assertSent(ReceiptMail::class, fn (ReceiptMail $mail) => $mail->hasTo('ops@naiprints.co.ke'));

        $transaction->refresh();
        $sentTo = collect($transaction->raw_request['order_email_sent_to'] ?? [])->map(fn ($email) => Str::lower((string) $email));
        $this->assertTrue($sentTo->contains('customer@example.com'));
        $this->assertTrue($sentTo->contains('sales@naiprints.co.ke'));
        $this->assertTrue((bool) ($transaction->raw_request['order_email_sent'] ?? false));
        $this->assertNotNull($transaction->payment_id);
    }

    private function createInvoice(string $email): Invoice
    {
        $client = Client::create([
            'name' => 'Jane Doe',
            'email' => $email,
            'phone' => '0700000000',
            'address' => 'Kimathi Street',
        ]);

        $sequence = (int) Invoice::max('sequence') + 1;
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'sequence' => $sequence,
            'number' => 'INV-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'public_id' => (string) Str::uuid(),
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_amount' => 0,
            'tax_rate' => null,
            'status' => 'unpaid',
        ]);

        $invoice->items()->create([
            'name' => 'Classic Tee',
            'quantity' => 2,
            'unit_price' => 1500,
            'total' => 3000,
            'position' => 0,
        ]);

        $invoice->load('items', 'payments');
        $invoice->recalculateTotals();
        $invoice->refreshStatus();
        $invoice->save();

        return $invoice;
    }
}
