<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $verificationUrl = URL::signedRoute('invoice.verify', $invoice->public_id);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verificationUrl);

        return view('invoices.public', compact('invoice', 'settings', 'verificationUrl', 'qrUrl'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $verificationUrl = URL::signedRoute('invoice.verify', $invoice->public_id);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verificationUrl);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'settings', 'verificationUrl', 'qrUrl'))
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("{$invoice->number}.pdf");
    }

    public function verify(Invoice $invoice)
    {
        $invoice->load(['client']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());

        return view('invoices.verify', compact('invoice', 'settings'));
    }
}
