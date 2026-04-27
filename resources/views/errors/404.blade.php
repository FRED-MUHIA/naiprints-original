<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Page Not Found | Nai Prints</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#111827] text-white">
        <main class="flex min-h-screen items-center justify-center px-4 py-16">
            <section class="mx-auto max-w-xl text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-white/60">404</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">Page not found</h1>
                <p class="mx-auto mt-4 max-w-md text-base leading-7 text-white/70">
                    The page you are looking for may have moved, expired, or does not exist.
                </p>
                <a href="{{ url('/') }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">
                    Return Home
                </a>
            </section>
        </main>
    </body>
</html>
