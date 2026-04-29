<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Affiliate Login | Nai Prints</title>
        <meta name="robots" content="noindex, nofollow">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <main class="mx-auto grid min-h-screen max-w-md place-items-center px-4 py-10">
            <section class="w-full rounded-[28px] bg-white p-6 shadow-2xl md:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Nai Prints</p>
                <h1 class="mt-2 text-3xl font-bold">Affiliate login</h1>

                @if (session('success'))
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('affiliate.login.submit') }}" class="mt-6 grid gap-5">
                    @csrf
                    <label class="checkout-field">
                        <span class="checkout-label">Email</span>
                        <input class="checkout-input" type="email" name="email" value="{{ old('email') }}" required>
                    </label>
                    <label class="checkout-field">
                        <span class="checkout-label">Password</span>
                        <input class="checkout-input" type="password" name="password" required>
                    </label>
                    <button class="btn-brand" type="submit">Login</button>
                </form>
                <p class="mt-5 text-sm text-slate-600">New affiliate? <a class="font-semibold text-brand" href="{{ route('affiliate.register') }}">Register here</a>.</p>
            </section>
        </main>
    </body>
</html>
