<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());

        return view('invoices.public', compact('invoice', 'settings'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'settings'));

        return $pdf->download("{$invoice->number}.pdf");
    }
}
