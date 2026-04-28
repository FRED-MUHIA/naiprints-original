<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Support\DocumentSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_sequence_includes_soft_deleted_invoices(): void
    {
        $client = Client::create([
            'name' => 'Sequence Client',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'sequence' => 56,
            'number' => 'INV-0056',
            'public_id' => (string) Str::uuid(),
            'issue_date' => now()->toDateString(),
            'discount_amount' => 0,
            'tax_rate' => null,
        ]);

        $invoice->delete();

        $this->assertSame(57, DocumentSequence::next(Invoice::class, lock: false));
    }
}
