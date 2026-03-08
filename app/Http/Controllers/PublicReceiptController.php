<?php

namespace App\Http\Controllers;

use App\Models\InvoiceSetting;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicReceiptController extends Controller
{
    public function show(Receipt $receipt)
    {
        $receipt->load(['invoice.client', 'payment']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());

        return view('receipts.public', compact('receipt', 'settings'));
    }

    public function pdf(Receipt $receipt)
    {
        $receipt->load(['invoice.client', 'payment']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $pdf = Pdf::loadView('receipts.pdf', compact('receipt', 'settings'));

        return $pdf->download("{$receipt->number}.pdf");
    }
}
