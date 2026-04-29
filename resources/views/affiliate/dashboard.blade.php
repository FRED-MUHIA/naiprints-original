<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Affiliate Dashboard | Nai Prints</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="mx-auto max-w-7xl px-4 py-8">
            <header class="mb-6 rounded-[28px] bg-white p-6 shadow-lg">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Affiliate Dashboard</p>
                        <h1 class="mt-2 text-3xl font-bold">Welcome, {{ $affiliate->full_name }}</h1>
                    </div>
                    <form method="POST" action="{{ route('affiliate.logout') }}">
                        @csrf
                        <button class="btn-outline" type="submit">Logout</button>
                    </form>
                </div>
            </header>

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <section class="mb-6 rounded-[24px] bg-white p-6 shadow-lg">
                <label class="checkout-label">Your referral link</label>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                    <input id="affiliate-link" class="checkout-input flex-1" readonly value="{{ $affiliate->referral_url }}">
                    <button class="btn-brand" type="button" onclick="navigator.clipboard.writeText(document.getElementById('affiliate-link').value)">Copy link</button>
                </div>
            </section>

            @if ($referralProducts->isNotEmpty())
                <section class="mb-6 rounded-[24px] bg-white p-6 shadow-lg">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold">Product referral links</h2>
                            <p class="mt-1 text-sm text-slate-600">Copy a link for the exact product you want to promote.</p>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @foreach ($referralProducts as $product)
                            @php
                                $productReferralUrl = route('products.show', ['product' => $product->slug ?: $product->id]) . '?ref=' . urlencode($affiliate->referral_code);
                                $inputId = 'product-referral-' . $product->id;
                            @endphp
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500">KES {{ number_format((float) $product->price, 2) }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a class="admin-btn-outline" href="{{ $productReferralUrl }}" target="_blank" rel="noopener">Open</a>
                                        <button class="admin-btn-outline" type="button" onclick="navigator.clipboard.writeText(document.getElementById('{{ $inputId }}').value)">Copy</button>
                                    </div>
                                </div>
                                <input id="{{ $inputId }}" class="checkout-input mt-3 text-xs" readonly value="{{ $productReferralUrl }}">
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    'Clicks' => $clicks,
                    'Paid orders' => $orders,
                    'Pending' => 'KES ' . number_format((float) ($commissionSums['pending'] ?? 0), 2),
                    'Approved' => 'KES ' . number_format((float) ($commissionSums['approved'] ?? 0), 2),
                    'Paid' => 'KES ' . number_format((float) ($commissionSums['paid'] ?? 0), 2),
                    'Conversion' => $conversionRate . '%',
                ] as $label => $value)
                    <article class="rounded-[20px] bg-white p-5 shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $label }}</p>
                        <p class="mt-3 text-2xl font-bold">{{ $value }}</p>
                    </article>
                @endforeach
            </section>

            <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
                <section class="rounded-[24px] bg-white p-6 shadow-lg">
                    <h2 class="text-xl font-bold">Referred orders</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead class="text-xs uppercase tracking-[0.2em] text-slate-500">
                                <tr><th class="py-3">Invoice</th><th>Customer</th><th>Status</th><th>Total</th><th>Commission</th><th>Date</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($referredOrders as $order)
                                    <tr>
                                        <td class="py-3 font-semibold">{{ $order->number }}</td>
                                        <td>{{ $order->client?->display_name }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                                        <td>KES {{ number_format((float) $order->total, 2) }}</td>
                                        <td>
                                            @if ($order->affiliateCommissions->isNotEmpty())
                                                <div class="grid gap-1">
                                                    <span>KES {{ number_format((float) $order->affiliateCommissions->sum('amount'), 2) }}</span>
                                                    <span class="text-xs text-slate-500">
                                                        {{ $order->affiliateCommissions->pluck('product.name')->filter()->join(', ') ?: 'Product referral' }}
                                                    </span>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at?->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-4 text-slate-500" colspan="6">No referred orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $referredOrders->links() }}</div>
                </section>

                <aside class="grid gap-6">
                    <section class="rounded-[24px] bg-white p-6 shadow-lg">
                        <h2 class="text-xl font-bold">Request payout</h2>
                        <p class="mt-2 text-sm text-slate-600">Available approved balance: KES {{ number_format($availableBalance, 2) }}</p>
                        <p class="text-sm text-slate-600">Minimum payout: KES {{ number_format($minimumPayout, 2) }}</p>
                        <form method="POST" action="{{ route('affiliate.payouts.store') }}" class="mt-5 grid gap-4">
                            @csrf
                            <input class="checkout-input" name="amount" type="number" step="0.01" min="1" placeholder="Amount">
                            <input class="checkout-input" name="payment_method" value="{{ old('payment_method', $affiliate->payment_method) }}" placeholder="Payment method">
                            <textarea class="checkout-input" name="payment_details" rows="3" placeholder="Payment details">{{ old('payment_details', $affiliate->payment_details) }}</textarea>
                            <button class="btn-brand" type="submit">Submit request</button>
                        </form>
                    </section>
                    <section class="rounded-[24px] bg-white p-6 shadow-lg">
                        <h2 class="text-xl font-bold">Payment history</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($payouts as $payout)
                                <div class="rounded-2xl border border-slate-200 p-4 text-sm">
                                    <div class="flex justify-between gap-3 font-semibold">
                                        <span>KES {{ number_format((float) $payout->amount, 2) }}</span>
                                        <span>{{ ucfirst($payout->status) }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $payout->created_at?->format('M d, Y') }} via {{ $payout->payment_method }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No payout requests yet.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </body>
</html>
