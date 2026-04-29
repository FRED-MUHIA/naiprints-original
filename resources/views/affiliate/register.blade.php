<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Affiliate Registration | Nai Prints</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <main class="mx-auto grid min-h-screen max-w-5xl place-items-center px-4 py-10">
            <section class="w-full rounded-[28px] bg-white p-6 shadow-2xl md:p-10">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Nai Prints</p>
                        <h1 class="mt-2 text-3xl font-bold">Affiliate registration</h1>
                    </div>
                    <a class="btn-outline" href="{{ route('affiliate.login') }}">Login</a>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('affiliate.register.submit') }}" class="grid gap-5 md:grid-cols-2">
                    @csrf
                    <label class="checkout-field">
                        <span class="checkout-label">Full name</span>
                        <input class="checkout-input" name="full_name" value="{{ old('full_name') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Email</span>
                        <input class="checkout-input" type="email" name="email" value="{{ old('email') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Phone number</span>
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
                        <textarea class="checkout-input" name="payment_details" rows="4" required>{{ old('payment_details') }}</textarea>
                    </label>
                    <div class="md:col-span-2">
                        <button class="btn-brand" type="submit">Submit application</button>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
