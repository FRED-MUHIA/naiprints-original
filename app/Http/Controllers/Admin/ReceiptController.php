<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;

class ReceiptController extends Controller
{
    public function index()
    {
        $receipts = Receipt::with(['invoice.client', 'payment'])
            ->orderByDesc('payment_date')
            ->paginate(15);

        return view('admin.receipts.index', compact('receipts'));
    }
}
