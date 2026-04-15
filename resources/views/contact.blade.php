<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = $globalHeader['favicon_url'] ?? '';
            $faviconUrl = $faviconUrl ?: '/images/logo.svg';
            $supportEmail = $support['email'] ?? 'info@naiprints.co.ke';
            $supportPhone = $support['phone'] ?? '0700 000 000';
            $supportBrand = $support['brand'] ?? 'Nai Prints';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Contact Us | Nai Prints</title>
        <meta name="description" content="Contact Nai Prints for custom printing, branding, and bulk orders in Kenya.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-12">
                <section class="hero-card">
                    <p class="pill">Contact</p>
                    <h1 class="mt-6 text-4xl font-semibold leading-tight text-white md:text-5xl">
                        Talk to {{ $supportBrand }}
                    </h1>
                    <p class="mt-4 max-w-3xl text-sm text-white/85 md:text-base">
                        Send us your question, order details, or branding request. Our team will get back to you shortly.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3 text-sm text-white/90">
                        <a href="mailto:{{ $supportEmail }}" class="btn-secondary">{{ $supportEmail }}</a>
                        <a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}" class="btn-secondary">{{ $supportPhone }}</a>
                    </div>
                </section>

                <section class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                    @if (session('status'))
                        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="post" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <input type="hidden" name="source_url" value="{{ old('source_url', url()->current()) }}">

                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-semibold text-slate-700">Full name</span>
                            <input
                                type="text"
                                name="full_name"
                                value="{{ old('full_name') }}"
                                required
                                class="rounded-xl border border-slate-200 px-4 py-2.5 focus:border-brand focus:outline-none">
                        </label>

                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-semibold text-slate-700">Email</span>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                class="rounded-xl border border-slate-200 px-4 py-2.5 focus:border-brand focus:outline-none">
                        </label>

                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-semibold text-slate-700">Phone (optional)</span>
                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 focus:border-brand focus:outline-none">
                        </label>

                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-semibold text-slate-700">Subject</span>
                            <input
                                type="text"
                                name="subject"
                                value="{{ old('subject') }}"
                                required
                                class="rounded-xl border border-slate-200 px-4 py-2.5 focus:border-brand focus:outline-none">
                        </label>

                        <label class="flex flex-col gap-2 text-sm md:col-span-2">
                            <span class="font-semibold text-slate-700">Message</span>
                            <textarea
                                name="message"
                                rows="6"
                                required
                                class="rounded-xl border border-slate-200 px-4 py-2.5 focus:border-brand focus:outline-none">{{ old('message') }}</textarea>
                        </label>

                        <div class="md:col-span-2">
                            <button type="submit" class="btn-brand">Send message</button>
                        </div>
                    </form>
                </section>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
