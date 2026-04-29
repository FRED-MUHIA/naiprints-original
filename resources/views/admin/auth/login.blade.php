<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/svg+xml" href="/images/logo.svg">
        <link rel="shortcut icon" href="/images/logo.svg">
        <title>Admin Login | Nai Prints</title>
        <meta name="robots" content="noindex, nofollow">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="admin-card w-full max-w-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand text-white font-semibold">
                        NP
                    </div>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em]">Nai Prints</p>
                        <p class="text-xs text-slate-500">Admin access</p>
                    </div>
                </div>

                <h1 class="mt-6 text-2xl font-semibold">Sign in</h1>
                <p class="mt-2 text-sm text-slate-500">Use your admin username or editor email to continue.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Username or Email</label>
                        <input type="text" name="username" value="{{ old('username') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Password</label>
                        <input type="password" name="password" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                    </div>
                    <button type="submit" class="w-full admin-btn-primary">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>
