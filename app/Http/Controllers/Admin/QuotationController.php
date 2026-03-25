<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InvoiceSetting;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $quotations = Quotation::with(['client'])
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
            'total' => Quotation::count(),
            'draft' => Quotation::where('status', 'draft')->count(),
            'sent' => Quotation::where('status', 'sent')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
        ];

        return view('admin.quotations.index', compact('quotations', 'search', 'status', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $nextSequence = (int) Quotation::max('sequence') + 1;
        $nextNumber = $this->formatQuotationNumber($nextSequence);

        return view('admin.quotations.create', compact('clients', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $this->validateQuotation($request);

        $quotation = DB::transaction(function () use ($data) {
            $clientId = $data['client_id'] ?? null;
            if (!$clientId) {
                $client = Client::create($data['client_new']);
                $clientId = $client->id;
            }

            $sequence = (int) Quotation::max('sequence') + 1;
            $quotation = Quotation::create([
                'client_id' => $clientId,
                'sequence' => $sequence,
                'number' => $this->formatQuotationNumber($sequence),
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'status' => $data['status'],
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

            $quotation->items()->createMany($items->all());

            $quotation->load('items');
            $quotation->recalculateTotals();
            $quotation->save();

            return $quotation;
        });

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['client', 'items']);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['client', 'items']);
        $clients = Client::orderBy('name')->get();

        return view('admin.quotations.edit', compact('quotation', 'clients'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $this->validateQuotation($request, $quotation);

        DB::transaction(function () use ($quotation, $data) {
            $clientId = $data['client_id'] ?? null;
            if (!$clientId) {
                $client = Client::create($data['client_new']);
                $clientId = $client->id;
            }

            $quotation->update([
                'client_id' => $clientId,
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'status' => $data['status'],
                'tax_rate' => $data['tax_rate'],
                'discount_amount' => $data['discount_amount'],
                'notes' => $data['notes'],
                'terms' => $data['terms'],
            ]);

            $quotation->items()->delete();
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
            $quotation->items()->createMany($items->all());

            $quotation->load('items');
            $quotation->recalculateTotals();
            $quotation->save();
        });

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return redirect()->route('admin.quotations.index')
            ->with('success', 'Quotation moved to trash.');
    }

    public function trash()
    {
        $quotations = Quotation::onlyTrashed()
            ->with('client')
            ->orderByDesc('deleted_at')
            ->paginate(12);

        return view('admin.quotations.trash', compact('quotations'));
    }

    public function restore(int $quotationId)
    {
        $quotation = Quotation::onlyTrashed()->findOrFail($quotationId);
        $quotation->restore();

        return redirect()->route('admin.quotations.trash')
            ->with('success', 'Quotation restored.');
    }

    public function forceDelete(int $quotationId)
    {
        $quotation = Quotation::onlyTrashed()->findOrFail($quotationId);
        $quotation->forceDelete();

        return redirect()->route('admin.quotations.trash')
            ->with('success', 'Quotation permanently deleted.');
    }

    public function download(Quotation $quotation)
    {
        $quotation->load(['client', 'items']);
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
        $pdf = Pdf::loadView('quotations.pdf', compact('quotation', 'settings'))
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("{$quotation->number}.pdf");
    }

    public function export(Request $request)
    {
        $status = $request->string('status')->toString();

        $query = Quotation::with('client')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('issue_date');

        $filename = 'quotations-export-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Quotation #', 'Client', 'Issue Date', 'Valid Until', 'Status', 'Total']);

            $query->chunk(200, function ($quotations) use ($handle) {
                foreach ($quotations as $quotation) {
                    fputcsv($handle, [
                        $quotation->number,
                        $quotation->client?->display_name,
                        optional($quotation->issue_date)->format('Y-m-d'),
                        optional($quotation->valid_until)->format('Y-m-d'),
                        $quotation->status,
                        $quotation->total,
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    private function validateQuotation(Request $request, ?Quotation $quotation = null): array
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_new.name' => ['required_without:client_id', 'string', 'max:255'],
            'client_new.company_name' => ['nullable', 'string', 'max:255'],
            'client_new.phone' => ['nullable', 'string', 'max:255'],
            'client_new.email' => ['nullable', 'email', 'max:255'],
            'client_new.address' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'rejected'])],
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

    private function formatQuotationNumber(int $sequence): string
    {
        return 'QTN-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
