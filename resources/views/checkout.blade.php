<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = $globalHeader['favicon_url'] ?? '';
            $faviconUrl = $faviconUrl ?: '/images/logo.svg';
            $basePath = rtrim((string) request()->getBasePath(), '/');
            if (!preg_match('/^https?:\/\//i', $faviconUrl) && !str_starts_with($faviconUrl, '//') && str_starts_with($faviconUrl, '/')) {
                $faviconUrl = ($basePath ?: '').$faviconUrl;
            }
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Checkout | Nai Prints</title>
        <meta name="description" content="Secure checkout for your Nai Prints order.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen" data-checkout-page @if (session('order_success')) data-order-success="1" @endif>
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-10">
                @if (session('order_success'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('order_success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold">We could not place your order:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-8">
                    @php
                        $checkoutHeader = collect($pageContent['sections'] ?? [])->firstWhere('type', 'shop_header');
                        $checkoutHeaderData = $checkoutHeader['data'] ?? [];
                    @endphp
                    <p class="section-kicker">{{ $checkoutHeaderData['subtitle'] ?? 'Finalize your order' }}</p>
                    <h1 class="section-title">{{ $checkoutHeaderData['title'] ?? 'Checkout' }}</h1>
                </div>

                <form class="checkout-grid" method="POST" action="{{ route('checkout.submit') }}" data-checkout-form>
                    @csrf
                    <section class="checkout-form">
                        <div class="checkout-card">
                            <h2 class="text-lg font-semibold">Customer details</h2>
                            <div class="checkout-fields">
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-name">Full name</label>
                                    <input class="checkout-input" id="checkout-name" name="name" type="text" placeholder="Jane Doe" value="{{ old('name') }}">
                                </div>
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-email">Email address</label>
                                    <input class="checkout-input" id="checkout-email" name="email" type="email" placeholder="you@email.com" value="{{ old('email') }}">
                                </div>
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-phone">Phone number</label>
                                    <input class="checkout-input" id="checkout-phone" name="phone" type="tel" placeholder="+254 700 000 000" value="{{ old('phone') }}">
                                </div>
                            </div>
                        </div>

                        <div class="checkout-card">
                            <h2 class="text-lg font-semibold">Delivery</h2>
                            <div class="checkout-fields">
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-address">Street address</label>
                                    <input class="checkout-input" id="checkout-address" name="address" type="text" placeholder="Street, building, floor" value="{{ old('address') }}">
                                </div>
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-city">City</label>
                                    <input class="checkout-input" id="checkout-city" name="city" type="text" placeholder="Nairobi" value="{{ old('city') }}">
                                </div>
                                <div class="checkout-field">
                                    <label class="checkout-label" for="checkout-notes">Delivery notes</label>
                                    <textarea class="checkout-input" id="checkout-notes" name="notes" rows="3" placeholder="Landmark, delivery time, etc.">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-card">
                            <h2 class="text-lg font-semibold">Payment</h2>
                            <div class="checkout-fields">
                                <label class="checkout-choice">
                                    <input type="radio" name="payment" value="delivery" @if (old('payment', 'delivery') === 'delivery') checked @endif>
                                    <span>Pay on delivery</span>
                                </label>
                                <label class="checkout-choice">
                                    <input type="radio" name="payment" value="mobile_money" @if (old('payment') === 'mobile_money') checked @endif>
                                    <span>Mobile money</span>
                                </label>
                                <label class="checkout-choice">
                                    <input type="radio" name="payment" value="card" @if (old('payment') === 'card') checked @endif>
                                    <span>Card payment</span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <aside class="checkout-summary">
                        <div class="checkout-card">
                            <div class="checkout-summary-header">
                                <h2 class="text-lg font-semibold">Order summary</h2>
                                <span class="text-xs text-slate-500">Updated live</span>
                            </div>
                            <div class="checkout-empty" data-checkout-empty>
                                <p class="text-sm text-slate-500">Your cart is empty.</p>
                                <a class="btn-outline" href="{{ route('shop') }}">Back to shop</a>
                            </div>
                            <ul class="checkout-items" data-checkout-items></ul>
                            <div class="checkout-totals">
                                <div class="checkout-row">
                                    <span>Subtotal</span>
                                    <span data-checkout-subtotal>KSh 0.00</span>
                                </div>
                                <div class="checkout-row">
                                    <span>Delivery</span>
                                    <span class="text-slate-500">Calculated at checkout</span>
                                </div>
                                <div class="checkout-row checkout-total">
                                    <span>Total</span>
                                    <span data-checkout-total>KSh 0.00</span>
                                </div>
                            </div>
                            <input type="hidden" name="cart_items" data-checkout-items-field>
                            <input type="hidden" name="cart_subtotal" data-checkout-subtotal-field>
                            <input type="hidden" name="cart_total" data-checkout-total-field>
                            <button class="btn-brand w-full justify-center" type="submit" data-checkout-submit>Place order</button>
                            <p class="mt-3 text-xs text-slate-500">By placing your order you agree to our terms and privacy policy.</p>
                        </div>
                    </aside>
                </form>
            </main>

            @include('partials.site-footer', [
                'globalFooter' => $globalFooter ?? [],
                'globalPurchasePopups' => $globalPurchasePopups ?? [],
                'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
            ])
        </div>
    </body>
</html>
