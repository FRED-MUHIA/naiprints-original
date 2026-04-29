<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Affiliate Registration | Nai Prints</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <main class="mx-auto grid min-h-screen max-w-3xl place-items-center px-4 py-8">
            <section class="w-full rounded-2xl border border-slate-200 bg-white p-5 shadow-lg md:p-7">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Nai Prints</p>
                        <h1 class="mt-1 text-2xl font-bold">Affiliate registration</h1>
                    </div>
                    <a class="btn-outline" href="{{ route('affiliate.login') }}">Login</a>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('affiliate.register.submit') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <label class="checkout-field">
                        <span class="checkout-label">Name</span>
                        <input class="checkout-input" name="full_name" value="{{ old('full_name') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Email</span>
                        <input class="checkout-input" type="email" name="email" value="{{ old('email') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Phone</span>
                        <input class="checkout-input" name="phone" value="{{ old('phone') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Payment method</span>
                        <input class="checkout-input" name="payment_method" value="{{ old('payment_method', 'M-Pesa') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Password</span>
                        <input class="checkout-input" type="password" name="password" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Confirm password</span>
                        <input class="checkout-input" type="password" name="password_confirmation" required>
                    </label>
                    <label class="checkout-field md:col-span-2">
                        <span class="checkout-label">Payment details</span>
                        <textarea class="checkout-input" name="payment_details" rows="3" required>{{ old('payment_details') }}</textarea>
                    </label>
                    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm leading-6 text-slate-700">
                            Before applying, read and accept the affiliate rules. Links must not be used for scams, impersonation, spam,
                            fake orders, misleading discounts, or unlawful promotions.
                        </p>
                        <div class="mt-2 flex flex-wrap gap-3 text-sm font-semibold">
                            <a class="text-brand underline underline-offset-4" href="{{ route('affiliate-policies') }}" target="_blank" rel="noopener">Read affiliate policies</a>
                            <a class="text-brand underline underline-offset-4" href="{{ route('data-protection-policies') }}" target="_blank" rel="noopener">Read data protection policies</a>
                            <a class="text-brand underline underline-offset-4" href="{{ route('ethical-policies') }}" target="_blank" rel="noopener">Read ethical policies</a>
                        </div>
                        <label class="mt-3 flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm font-semibold text-slate-800">
                            <input class="mt-1" type="checkbox" name="policy_acceptance" value="1" required @checked(old('policy_acceptance'))>
                            <span>
                                I have read and accept the policies, and agree not to misuse my affiliate link.
                            </span>
                        </label>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 md:col-span-2">
                        <button class="btn-brand" type="submit">Submit application</button>
                        <p class="text-sm text-slate-600">Already approved? <a class="font-semibold text-brand" href="{{ route('affiliate.login') }}">Login</a></p>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
