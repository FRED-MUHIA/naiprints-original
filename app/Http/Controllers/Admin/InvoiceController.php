<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSetting;
use App\Services\InvoicePaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $invoices = Invoice::with(['client'])
            ->when($search, function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('issue_date')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'partial' => Invoice::where('status', 'partially_paid')->count(),
            'unpaid' => Invoice::where('status', 'unpaid')->count(),
        ];

        return view('admin.invoices.index', compact('invoices', 'search', 'status', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $nextSequence = (int) Invoice::max('sequence') + 1;
        $nextNumber = $this->formatInvoiceNumber($nextSequence);

        return view('admin.invoices.create', compact('clients', 'settings', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);

        $invoice = DB::transaction(function () use ($data) {
            $clientId = $data['client_id'] ?? null;
            if (!$clientId) {
                $client = Client::create($data['client_new']);
                $clientId = $client->id;
            }

            $sequence = (int) Invoice::max('sequence') + 1;
            $invoice = Invoice::create([
                'client_id' => $clientId,
                'sequence' => $sequence,
                'number' => $this->formatInvoiceNumber($sequence),
                'public_id' => (string) Str::uuid(),
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'tax_rate' => $data['tax_rate'],
                'discount_amount' => $data['discount_amount'],
                'notes' => $data['notes'],
                'terms' => $data['terms'],
            ]);

            $items = collect($data['items'])->values()->map(function ($item, $index) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                return [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => round($quantity * $unitPrice, 2),
                    'position' => $index,
                ];
            });

            $invoice->items()->createMany($items->all());

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments.receipt', 'receipts', 'mpesaTransactions']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());

        return view('admin.invoices.show', compact('invoice', 'settings'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['client', 'items']);
        $clients = Client::orderBy('name')->get();
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());

        return view('admin.invoices.edit', compact('invoice', 'clients', 'settings'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateInvoice($request, $invoice);

        DB::transaction(function () use ($invoice, $data) {
            $clientId = $data['client_id'] ?? null;
            if (!$clientId) {
                $client = Client::create($data['client_new']);
                $clientId = $client->id;
            }

            $invoice->update([
                'client_id' => $clientId,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'tax_rate' => $data['tax_rate'],
                'discount_amount' => $data['discount_amount'],
                'notes' => $data['notes'],
                'terms' => $data['terms'],
            ]);

            $invoice->items()->delete();
            $items = collect($data['items'])->values()->map(function ($item, $index) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                return [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => round($quantity * $unitPrice, 2),
                    'position' => $index,
                ];
            });
            $invoice->items()->createMany($items->all());

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice moved to trash.');
    }

    public function trash()
    {
        $invoices = Invoice::onlyTrashed()
            ->with('client')
            ->orderByDesc('deleted_at')
            ->paginate(12);

        return view('admin.invoices.trash', compact('invoices'));
    }

    public function restore(int $invoiceId)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($invoiceId);
        $invoice->restore();

        return redirect()->route('admin.invoices.trash')
            ->with('success', 'Invoice restored.');
    }

    public function forceDelete(int $invoiceId)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($invoiceId);
        $invoice->forceDelete();

        return redirect()->route('admin.invoices.trash')
            ->with('success', 'Invoice permanently deleted.');
    }

    public function addPayment(Request $request, Invoice $invoice, InvoicePaymentService $payments)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $minDeposit = round(((float) $invoice->total) * 0.6, 2);
        if ($invoice->amount_paid <= 0 && $data['amount'] < $minDeposit) {
            return back()->withErrors([
                'amount' => 'The first payment must be at least 60% of the invoice total (KES ' . number_format($minDeposit, 2) . ').',
            ])->withInput();
        }

        $payments->record($invoice, $data);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment recorded and receipt generated.');
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments', 'receipts']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $verificationUrl = URL::signedRoute('invoice.verify', $invoice->public_id);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verificationUrl);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'settings', 'verificationUrl', 'qrUrl'))
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("{$invoice->number}.pdf");
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $invoice->load('client');

        $data = $request->validate([
            'email' => ['nullable', 'email'],
        ]);

        $recipient = $data['email'] ?? $invoice->client?->email;

        if (!$recipient) {
            return back()->withErrors(['email' => 'This client does not have an email address.']);
        }

        Mail::to($recipient)->send(new InvoiceMail($invoice));

        return back()->with('success', 'Invoice emailed successfully.');
    }

    public function export(Request $request)
    {
        $status = $request->string('status')->toString();

        $query = Invoice::with('client')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('issue_date');

        $filename = 'invoices-export-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice #', 'Client', 'Issue Date', 'Due Date', 'Status', 'Total', 'Paid', 'Balance']);

            $query->chunk(200, function ($invoices) use ($handle) {
                foreach ($invoices as $invoice) {
                    fputcsv($handle, [
                        $invoice->number,
                        $invoice->client?->display_name,
                        optional($invoice->issue_date)->format('Y-m-d'),
                        optional($invoice->due_date)->format('Y-m-d'),
                        $invoice->status,
                        $invoice->total,
                        $invoice->amount_paid,
                        $invoice->balance,
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    private function validateInvoice(Request $request, ?Invoice $invoice = null): array
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_new.name' => ['required_without:client_id', 'string', 'max:255'],
            'client_new.company_name' => ['nullable', 'string', 'max:255'],
            'client_new.phone' => ['nullable', 'string', 'max:255'],
            'client_new.email' => ['nullable', 'email', 'max:255'],
            'client_new.address' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
        ]);

        $applyTax = $request->boolean('apply_tax');
        $data['tax_rate'] = $applyTax ? (float) ($data['tax_rate'] ?? 0) : null;
        $data['discount_amount'] = (float) ($data['discount_amount'] ?? 0);

        return $data;
    }

    private function formatInvoiceNumber(int $sequence): string
    {
        return 'INV-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function formatReceiptNumber(int $sequence): string
    {
        return 'RCPT-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
