@php
    use Illuminate\Support\Facades\Storage;
    $logoUrl = $settings->logo_path ? Storage::url($settings->logo_path) : null;
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->number }} - Nai Prints</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100 text-slate-900" style="--brand-red: {{ $settings->theme_color ?? '#e11d2e' }};">
    <div class="mx-auto max-w-5xl px-4 py-10">
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-3xl bg-white shadow-xl overflow-hidden">
            <div class="h-2 bg-brand"></div>
            @php
                $paymentMethods = collect($settings->payment_methods ?? [])->map(fn ($m) => trim((string) $m));
                $paymentMethodsLower = $paymentMethods->map(fn ($m) => strtolower($m));
                $showMpesaPay = $paymentMethodsLower->contains('m-pesa') || $paymentMethodsLower->contains('mpesa') || ($settings->mpesa_config['shortcode'] ?? null);
                $showBankDetails = (bool) trim((string) ($settings->bank_details ?? ''));
            @endphp
            <div class="p-8">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div class="flex items-center gap-4">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Nai Prints" class="h-14 w-14 rounded-full object-cover">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brand text-white text-lg font-bold">NP</div>
                        @endif
                        <div>
                            <h1 class="text-2xl font-semibold tracking-wide">{{ $settings->business_name }}</h1>
                            <p class="text-sm text-slate-500">{{ $settings->phone }} {{ $settings->email }}</p>
                            <p class="text-sm text-slate-500">{{ $settings->website }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand">Invoice</p>
                        <p class="text-lg font-semibold">{{ $invoice->number }}</p>
                        <p class="text-sm text-slate-500 mt-2">Date: {{ optional($invoice->issue_date)->format('M d, Y') }}</p>
                        <p class="text-sm text-slate-500">Due: {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-[1.2fr_0.8fr]">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Bill To</p>
                        <p class="mt-2 text-lg font-semibold">{{ $invoice->client?->name }}</p>
                        <p class="text-sm text-slate-500">{{ $invoice->client?->company_name }}</p>
                        <p class="text-sm text-slate-500">{{ $invoice->client?->phone }}</p>
                        <p class="text-sm text-slate-500">{{ $invoice->client?->email }}</p>
                        <p class="text-sm text-slate-500">{{ $invoice->client?->address }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-2">
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Status</span>
                            <span class="font-semibold text-slate-800">{{ str_replace('_', ' ', $invoice->status) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Total</span>
                            <span class="font-semibold text-slate-800">KES {{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Paid</span>
                            <span class="font-semibold text-slate-800">KES {{ number_format($invoice->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-slate-900">
                            <span>Balance</span>
                            <span>KES {{ number_format($invoice->balance, 2) }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white text-right">
                            Balance Due: KES {{ number_format($invoice->balance, 2) }}
                        </div>
                        @if (!empty($qrUrl))
                            <div class="pt-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Verify Invoice</p>
                                <div class="mt-2 flex items-center gap-3">
                                    <img src="{{ $qrUrl }}" alt="Verify invoice QR" class="h-20 w-20 rounded-xl bg-white p-2">
                                    <div class="text-xs text-slate-500">
                                        Scan to verify invoice origin.
                                        <div><a href="{{ $verificationUrl }}" class="text-brand underline">Open verification</a></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-[0.2em] text-white">
                            <tr class="bg-brand">
                                <th class="py-2 px-2">Item</th>
                                <th class="px-2">Description</th>
                                <th class="px-2">Qty</th>
                                <th class="px-2">Unit</th>
                                <th class="px-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700">
                            @foreach ($invoice->items as $item)
                                <tr class="border-t border-slate-200">
                                    <td class="py-3 px-2 font-semibold">{{ $item->name }}</td>
                                    <td class="px-2">{{ $item->description ?? '-' }}</td>
                                    <td class="px-2">{{ $item->quantity }}</td>
                                    <td class="px-2">KES {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-2 text-right">KES {{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-[1.2fr_0.8fr]">
                    <div class="space-y-3 text-sm text-slate-500">
                        @if ($settings->payment_methods)
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Payment Methods</p>
                            <ul class="space-y-2">
                                @foreach ($settings->payment_methods as $method)
                                    @php $methodLower = strtolower((string) $method); @endphp
                                    <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                                        <span class="text-slate-700">{{ $method }}</span>
                                        @if (in_array($methodLower, ['m-pesa', 'mpesa']) && $showMpesaPay)
                                            <button type="button"
                                                    id="mpesa-inline-open"
                                                    class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                                Pay now
                                            </button>
                                        @elseif ($methodLower === 'bank transfer' && $showBankDetails)
                                            <button type="button"
                                                    id="bank-details-toggle"
                                                    class="text-xs font-semibold text-slate-600 hover:text-brand">
                                                Bank details
                                            </button>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($showBankDetails)
                            <div id="bank-details-panel" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Bank Transfer</p>
                                <div class="mt-2 whitespace-pre-line">{{ $settings->bank_details }}</div>
                            </div>
                        @endif

                        @if ($showMpesaPay && ($settings->mpesa_config['shortcode'] ?? null))
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">M-Pesa PayBill</p>
                                <p class="mt-2 text-sm text-slate-700"><strong>PayBill:</strong> {{ $settings->mpesa_config['shortcode'] }}</p>
                                <p class="text-sm text-slate-700"><strong>Account:</strong> {{ $invoice->number }}</p>
                            </div>
                        @endif

                        @if ($settings->footer_text)
                            <p>{{ $settings->footer_text }}</p>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>KES {{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="mt-2 flex justify-between">
                            <span>Tax</span>
                            <span>KES {{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        <div class="mt-2 flex justify-between">
                            <span>Discount</span>
                            <span>KES {{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                        <div class="mt-3 flex justify-between text-base font-semibold text-slate-900">
                            <span>Total</span>
                            <span>KES {{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('invoice.public.pdf', $invoice->public_id) }}" class="btn-brand w-full justify-center">Download PDF</a>
                        </div>
                        @if ($showMpesaPay)
                            <div class="mt-3">
                                <button type="button"
                                        id="mpesa-open"
                                        class="inline-flex w-full items-center justify-center rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">
                                    Pay
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($showMpesaPay)
        <div id="mpesa-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Pay via M-Pesa</h2>
                    <button type="button" id="mpesa-close" class="text-slate-400 hover:text-slate-600">x</button>
                </div>
                <p class="mt-2 text-sm text-slate-500">Enter your M-Pesa number to receive the STK prompt.</p>
                <form method="POST" action="{{ route('invoice.public.mpesa', $invoice->public_id) }}" class="mt-4 space-y-4">
                    @csrf
                    <label class="space-y-2 text-sm font-semibold text-slate-600">
                        M-Pesa Phone Number
                        <input type="text" name="phone" value="{{ old('phone', $invoice->client?->phone) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2">
                    </label>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Amount: <strong>KES {{ number_format($invoice->balance, 2) }}</strong>
                    </div>
                    <button type="submit" class="btn-brand w-full justify-center">Send STK Prompt</button>
                </form>
            </div>
        </div>

        <script>
            const mpesaOpen = document.getElementById('mpesa-open');
            const mpesaInlineOpen = document.getElementById('mpesa-inline-open');
            const mpesaClose = document.getElementById('mpesa-close');
            const mpesaModal = document.getElementById('mpesa-modal');
            const bankToggle = document.getElementById('bank-details-toggle');
            const bankPanel = document.getElementById('bank-details-panel');

            if (mpesaOpen && mpesaClose && mpesaModal) {
                const toggle = (show) => {
                    mpesaModal.classList.toggle('hidden', !show);
                    mpesaModal.classList.toggle('flex', show);
                };

                mpesaOpen.addEventListener('click', () => toggle(true));
                if (mpesaInlineOpen) {
                    mpesaInlineOpen.addEventListener('click', () => toggle(true));
                }
                mpesaClose.addEventListener('click', () => toggle(false));
                mpesaModal.addEventListener('click', (event) => {
                    if (event.target === mpesaModal) {
                        toggle(false);
                    }
                });
            }

            if (bankToggle && bankPanel) {
                bankToggle.addEventListener('click', () => {
                    bankPanel.classList.toggle('hidden');
                });
            }
        </script>
    @endif
</body>
</html>
