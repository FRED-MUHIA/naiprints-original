<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminInvoicePaymentRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_invoice_payments_url_redirects_to_invoice_show_page(): void
    {
        $client = Client::create([
            'name' => 'Route Test Client',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'sequence' => 82,
            'number' => 'INV-0082',
            'public_id' => (string) Str::uuid(),
            'issue_date' => now()->toDateString(),
            'discount_amount' => 0,
            'tax_rate' => null,
        ]);

        $response = $this
            ->withSession([
                'admin_authenticated' => true,
                'admin_role' => 'admin',
            ])
            ->get(route('admin.invoices.payments.index', $invoice));

        $response->assertRedirect(route('admin.invoices.show', $invoice));
    }
}
