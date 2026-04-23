<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/svg+xml" href="/images/logo.svg">
        <link rel="shortcut icon" href="/images/logo.svg">
        <title>Nai Prints Admin</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        @php
            $adminRole = strtolower((string) session('admin_role', 'admin'));
            $isAdminRole = $adminRole === 'admin';
        @endphp
        <div class="flex min-h-screen gap-6 p-6">
            <aside class="w-72 shrink-0">
                <div class="admin-panel flex h-full flex-col p-5">
                    <div class="rounded-[24px] bg-brand px-4 py-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white/15 text-sm font-semibold">
                                NP
                            </div>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.2em]">Nai Prints</p>
                                <p class="text-xs text-white/70">Product management</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex-1">
                        <p class="px-3 text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Content management</p>
                        <nav class="mt-3 space-y-2 text-sm font-semibold">
                            @if ($isAdminRole)
                                <a href="{{ route('admin.dashboard') }}"
                                   class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                    Dashboard
                                </a>
                            @endif
                            <a href="{{ route('admin.products.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.products.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Products
                            </a>
                            @if ($isAdminRole)
                            <a href="{{ route('admin.blogs.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.blogs.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Blogs
                            </a>
                            <a href="{{ route('admin.blog-categories.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.blog-categories.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Blog Categories
                            </a>
                            <a href="{{ route('admin.blog-posters.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.blog-posters.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Blog Posters
                            </a>
                            <a href="{{ route('admin.categories.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.categories.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Catalogue
                            </a>
                            <a href="{{ route('admin.brands.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.brands.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Brands
                            </a>
                    <a href="{{ route('admin.tags.index') }}"
                       class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.tags.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        Tags
                    </a>
                    <a href="{{ route('admin.builder.index') }}"
                       class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.builder.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        Page Builder
                    </a>
                    <a href="{{ route('admin.media.index') }}"
                       class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.media.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        Media Library
                    </a>
                    <a href="{{ route('admin.attributes.index') }}"
                       class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.attributes.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        Attributes
                    </a>
                            <a href="{{ route('admin.sizes.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.sizes.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Sizes
                            </a>
                            @endif
                        </nav>

                        @if ($isAdminRole)
                        <p class="mt-8 px-3 text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Invoicing</p>
                        <nav class="mt-3 space-y-2 text-sm font-semibold">
                            <a href="{{ route('admin.invoices.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.invoices.index') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Dashboard Overview
                            </a>
                            <a href="{{ route('admin.clients.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.clients.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Clients
                            </a>
                            <a href="{{ route('admin.tasks.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.tasks.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Tasks
                            </a>
                            <a href="{{ route('admin.invoices.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.invoices.*') && !request()->routeIs('admin.invoices.index') && !request()->routeIs('admin.invoices.settings') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Invoices
                            </a>
                            <a href="{{ route('admin.quotations.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.quotations.*') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Quotations
                            </a>
                            <a href="{{ route('admin.payments.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.payments.index') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Payments
                            </a>
                            <a href="{{ route('admin.receipts.index') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.receipts.index') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Receipts
                            </a>
                            <a href="{{ route('admin.invoices.settings') }}"
                               class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.invoices.settings') ? 'bg-brand text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                                Settings
                            </a>
                        </nav>
                        @endif
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-xs text-slate-500">
                        {{ $isAdminRole ? 'Manage your catalog, pricing, and inventory in one place.' : 'Editor access: manage products only.' }}
                    </div>
                </div>
            </aside>

            <div class="flex-1 space-y-6">
                <header class="admin-card">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Nai Prints Admin</p>
                            <h1 class="mt-2 text-4xl font-semibold tracking-tight">@yield('title')</h1>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                            <a href="{{ $isAdminRole ? route('admin.dashboard') : route('admin.products.index') }}" class="admin-btn-primary text-xs uppercase tracking-[0.3em]">
                                {{ $isAdminRole ? 'Admin' : 'Editor' }}
                            </a>
                            <span class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">
                                {{ now()->format('M d, Y') }}
                            </span>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="admin-btn-outline text-xs uppercase tracking-[0.3em]">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="space-y-6 pb-6">
                    @if (session('success'))
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <p class="font-semibold">Please fix the following:</p>
                            <ul class="mt-2 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
        <script>
            (function () {
                const initClientAutofill = () => {
                    document.querySelectorAll('[data-client-autofill]').forEach((scope) => {
                        const select = scope.querySelector('[data-client-select]');
                        if (!select) {
                            return;
                        }

                        const fields = {
                            name: scope.querySelector('[data-client-field="name"]'),
                            company_name: scope.querySelector('[data-client-field="company_name"]'),
                            phone: scope.querySelector('[data-client-field="phone"]'),
                            email: scope.querySelector('[data-client-field="email"]'),
                            address: scope.querySelector('[data-client-field="address"]'),
                        };

                        const getValue = (option, key) => {
                            if (!option) {
                                return '';
                            }
                            const map = {
                                name: 'clientName',
                                company_name: 'clientCompanyName',
                                phone: 'clientPhone',
                                email: 'clientEmail',
                                address: 'clientAddress',
                            };
                            return option.dataset?.[map[key]] ?? '';
                        };

                        const applyClient = (clearWhenEmpty) => {
                            const selected = select.options[select.selectedIndex];
                            const hasClient = Boolean(select.value);

                            Object.entries(fields).forEach(([key, input]) => {
                                if (!input) {
                                    return;
                                }
                                if (!hasClient) {
                                    if (clearWhenEmpty) {
                                        input.value = '';
                                    }
                                    return;
                                }
                                input.value = getValue(selected, key);
                            });
                        };

                        select.addEventListener('change', () => applyClient(true));

                        const hasTypedValues = Object.values(fields).some((input) => (input?.value || '').trim() !== '');
                        if (select.value && !hasTypedValues) {
                            applyClient(false);
                        }
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initClientAutofill);
                } else {
                    initClientAutofill();
                }
            })();
        </script>
        @stack('scripts')
    </body>
</html>
