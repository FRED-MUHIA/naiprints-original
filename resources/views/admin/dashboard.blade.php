@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    @if (isset($isTaskModuleReady) && !$isTaskModuleReady)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Task module requires migration on this server. Run <code>php artisan migrate</code> in the project directory.
        </div>
    @endif

    @php
        $analytics = $analytics ?? [
            'period' => 'monthly',
            'start' => now()->startOfMonth()->toDateString(),
            'end' => now()->endOfMonth()->toDateString(),
            'currency' => 'KES',
            'locale' => str_replace('_', '-', app()->getLocale()),
            'summary' => [
                'revenue' => 0,
                'orders' => 0,
                'transactions' => 0,
                'average_transaction' => 0,
                'credits' => 0,
                'debits' => 0,
                'net' => 0,
                'production_cost' => 0,
                'materials_bought' => 0,
                'petty_cash' => 0,
            ],
            'trend' => [],
            'is_ready' => false,
            'finance_is_ready' => false,
            'recent_finance_entries' => collect(),
        ];
        $formatCurrency = function ($amount) use ($analytics) {
            if (class_exists(NumberFormatter::class)) {
                $formatter = new NumberFormatter($analytics['locale'], NumberFormatter::CURRENCY);
                return $formatter->formatCurrency((float) $amount, $analytics['currency']);
            }

            return $analytics['currency'].' '.number_format((float) $amount, 2);
        };
        $trendValues = collect($analytics['trend'])->pluck('value')->map(fn ($value) => (float) $value);
        $trendMax = max(1, $trendValues->max() ?? 0);
        $chartWidth = 720;
        $chartHeight = 210;
        $chartPadding = 18;
        $plotWidth = $chartWidth - ($chartPadding * 2);
        $plotHeight = $chartHeight - ($chartPadding * 2);
        $trendCount = max(1, count($analytics['trend']) - 1);
        $chartPoints = collect($analytics['trend'])->map(function ($point, $index) use ($chartPadding, $plotWidth, $plotHeight, $trendCount, $trendMax) {
            $x = $chartPadding + (($plotWidth / $trendCount) * $index);
            $y = $chartPadding + ($plotHeight - (((float) $point['value'] / $trendMax) * $plotHeight));
            return round($x, 2).','.round($y, 2);
        })->join(' ');
        $areaPoints = $chartPoints
            ? $chartPadding.','.($chartHeight - $chartPadding).' '.$chartPoints.' '.($chartWidth - $chartPadding).','.($chartHeight - $chartPadding)
            : '';
        $periodOptions = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'custom' => 'Custom',
        ];
    @endphp

    <section class="admin-card" data-performance-dashboard>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Performance</p>
                <h2 class="mt-2 text-2xl font-semibold">Business activity</h2>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2" data-performance-filter>
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <select name="analytics_period" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 focus:border-brand focus:outline-none" data-performance-period>
                    @foreach ($periodOptions as $value => $label)
                        <option value="{{ $value }}" @selected($analytics['period'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="{{ $analytics['period'] === 'custom' ? 'flex' : 'hidden' }} flex-wrap items-center gap-2" data-performance-custom>
                    <input type="date" name="analytics_start" value="{{ $analytics['start'] }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 focus:border-brand focus:outline-none">
                    <input type="date" name="analytics_end" value="{{ $analytics['end'] }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 focus:border-brand focus:outline-none">
                    <button type="submit" class="admin-btn-outline">Apply</button>
                </div>
            </form>
        </div>

        @if (!$analytics['is_ready'])
            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Payment analytics will appear after the invoicing tables are migrated.
            </div>
        @endif
        @if (!$analytics['finance_is_ready'])
            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Debit tracking requires migration. Run <code>php artisan migrate</code> to enable production, materials, and petty cash entries.
            </div>
        @endif

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(280px,0.95fr)_minmax(0,1.45fr)]">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total revenue</p>
                <p class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">{{ $formatCurrency($analytics['summary']['revenue']) }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ \Illuminate\Support\Carbon::parse($analytics['start'])->format('M d, Y') }} - {{ \Illuminate\Support\Carbon::parse($analytics['end'])->format('M d, Y') }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Orders</p>
                        <p class="mt-2 text-2xl font-semibold">{{ number_format($analytics['summary']['orders']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Transactions</p>
                        <p class="mt-2 text-2xl font-semibold">{{ number_format($analytics['summary']['transactions']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Average</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $formatCurrency($analytics['summary']['average_transaction']) }}</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Credits</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $formatCurrency($analytics['summary']['credits']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-700">Debits</p>
                        <p class="mt-2 text-2xl font-semibold text-red-700">{{ $formatCurrency($analytics['summary']['debits']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Net</p>
                        <p class="mt-2 text-2xl font-semibold {{ $analytics['summary']['net'] < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $formatCurrency($analytics['summary']['net']) }}</p>
                    </div>
                </div>
            </div>

            <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold">Revenue trend</h3>
                        <p class="text-xs text-slate-500">Grouped by {{ $analytics['group'] }}</p>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="admin-btn-outline">Detailed analytics</a>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 p-3">
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Revenue trend chart" class="h-64 w-full">
                        <defs>
                            <linearGradient id="performanceArea" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#e11d2e" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#e11d2e" stop-opacity="0.02" />
                            </linearGradient>
                        </defs>
                        <line x1="{{ $chartPadding }}" y1="{{ $chartHeight - $chartPadding }}" x2="{{ $chartWidth - $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#cbd5e1" stroke-width="1" />
                        @if ($chartPoints)
                            <polygon points="{{ $areaPoints }}" fill="url(#performanceArea)" />
                            <polyline points="{{ $chartPoints }}" fill="none" stroke="#e11d2e" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                            @foreach ($analytics['trend'] as $point)
                                @continue((float) $point['value'] <= 0)
                                @php
                                    $dotIndex = $loop->index;
                                    $dotX = $chartPadding + (($plotWidth / $trendCount) * $dotIndex);
                                    $dotY = $chartPadding + ($plotHeight - (((float) $point['value'] / $trendMax) * $plotHeight));
                                @endphp
                                <circle cx="{{ round($dotX, 2) }}" cy="{{ round($dotY, 2) }}" r="4" fill="#ffffff" stroke="#e11d2e" stroke-width="3">
                                    <title>{{ $point['label'] }}: {{ $formatCurrency($point['value']) }}</title>
                                </circle>
                            @endforeach
                        @endif
                    </svg>
                    <div class="mt-2 flex justify-between gap-3 text-xs font-semibold text-slate-400">
                        <span>{{ $analytics['trend'][0]['label'] ?? '' }}</span>
                        <span>{{ $analytics['trend'][count($analytics['trend']) - 1]['label'] ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(280px,0.95fr)_minmax(0,1.45fr)]">
            <form method="POST" action="{{ route('admin.financial-entries.store') }}" class="rounded-2xl border border-slate-200 bg-white p-5">
                @csrf
                <input type="hidden" name="analytics_period" value="{{ $analytics['period'] }}">
                <input type="hidden" name="analytics_start" value="{{ $analytics['start'] }}">
                <input type="hidden" name="analytics_end" value="{{ $analytics['end'] }}">
                @if (request('category'))
                    <input type="hidden" name="dashboard_category" value="{{ request('category') }}">
                @endif

                <div>
                    <h3 class="text-lg font-semibold">Record debit</h3>
                    <p class="text-xs text-slate-500">Production, materials, and petty cash costs.</p>
                </div>

                <div class="mt-4 grid gap-3">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Cost type</label>
                        <select name="category" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" @disabled(!$analytics['finance_is_ready'])>
                            <option value="production_cost">Production Cost</option>
                            <option value="materials_bought">Materials Bought</option>
                            <option value="petty_cash">Petty Cash</option>
                        </select>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Amount</label>
                            <input type="number" name="amount" min="0.01" step="0.01" placeholder="0.00" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" @disabled(!$analytics['finance_is_ready'])>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Date</label>
                            <input type="date" name="entry_date" value="{{ now()->toDateString() }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" @disabled(!$analytics['finance_is_ready'])>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Note</label>
                        <input type="text" name="description" placeholder="Optional detail" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" @disabled(!$analytics['finance_is_ready'])>
                    </div>
                    <button type="submit" class="admin-btn-primary justify-self-start" @disabled(!$analytics['finance_is_ready'])>Save Cost</button>
                </div>
            </form>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Production</p>
                        <p class="mt-2 text-xl font-semibold">{{ $formatCurrency($analytics['summary']['production_cost']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Materials</p>
                        <p class="mt-2 text-xl font-semibold">{{ $formatCurrency($analytics['summary']['materials_bought']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Petty Cash</p>
                        <p class="mt-2 text-xl font-semibold">{{ $formatCurrency($analytics['summary']['petty_cash']) }}</p>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Cost</th>
                                <th>Date</th>
                                <th>Debit</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($analytics['recent_finance_entries'] ?? collect()) as $entry)
                                <tr data-finance-row="{{ $entry->id }}">
                                    <td>
                                        <span class="font-semibold text-slate-700">{{ $entry->category_label }}</span>
                                        @if ($entry->description)
                                            <span class="block text-xs text-slate-500">{{ $entry->description }}</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($entry->entry_date)->format('M d, Y') }}</td>
                                    <td class="font-semibold text-red-700">{{ $formatCurrency($entry->amount) }}</td>
                                    <td class="text-right">
                                        <button type="button" class="admin-btn-outline" data-finance-edit="{{ $entry->id }}">Edit</button>
                                    </td>
                                </tr>
                                <tr class="hidden bg-slate-50" data-finance-edit-row="{{ $entry->id }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('admin.financial-entries.update', $entry) }}" class="grid gap-3 p-3 md:grid-cols-[1fr_1fr_1fr_1.2fr_auto]">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="analytics_period" value="{{ $analytics['period'] }}">
                                            <input type="hidden" name="analytics_start" value="{{ $analytics['start'] }}">
                                            <input type="hidden" name="analytics_end" value="{{ $analytics['end'] }}">
                                            @if (request('category'))
                                                <input type="hidden" name="dashboard_category" value="{{ request('category') }}">
                                            @endif
                                            <select name="category" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                                <option value="production_cost" @selected($entry->category === 'production_cost')>Production Cost</option>
                                                <option value="materials_bought" @selected($entry->category === 'materials_bought')>Materials Bought</option>
                                                <option value="petty_cash" @selected($entry->category === 'petty_cash')>Petty Cash</option>
                                            </select>
                                            <input type="date" name="entry_date" value="{{ optional($entry->entry_date)->toDateString() }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                            <input type="number" name="amount" min="0.01" step="0.01" value="{{ $entry->amount }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                            <input type="text" name="description" value="{{ $entry->description }}" placeholder="Optional detail" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                            <div class="flex gap-2">
                                                <button type="submit" class="admin-btn-primary">Save</button>
                                                <button type="button" class="admin-btn-outline" data-finance-cancel="{{ $entry->id }}">Cancel</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="admin-empty">No debit entries for this period.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Task management</p>
                <h2 class="mt-2 text-2xl font-semibold">Production Tasks</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.tasks.index') }}" class="admin-btn-outline">Open Task Board</a>
                <a href="{{ route('admin.tasks.create') }}" class="admin-btn-primary">Assign New Task</a>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total</p>
                <p class="mt-2 text-2xl font-semibold">{{ $taskStats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Assigned</p>
                <p class="mt-2 text-2xl font-semibold">{{ $taskStats['assigned'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">In Progress</p>
                <p class="mt-2 text-2xl font-semibold text-blue-700">{{ $taskStats['in_progress'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Finished</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $taskStats['finished'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Delivered</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $taskStats['delivered'] ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Client</th>
                        <th>Assigned Time</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($recentTasks ?? collect()) as $task)
                        @php
                            $statusClass = match ($task->status) {
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'finished' => 'bg-teal-100 text-teal-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $task->reference }} - {{ $task->title }}</td>
                            <td>{{ $task->client?->display_name ?? '-' }}</td>
                            <td>{{ optional($task->assigned_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                Stage {{ $task->current_stage }}/{{ $task->total_stages }}
                                <span class="text-xs text-slate-500">({{ $task->progress_percent }}%)</span>
                            </td>
                            <td>
                                <span class="admin-status {{ $statusClass }}">{{ $task->status_label }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="admin-btn-outline">Update</a>
                                    <a href="{{ route('admin.tasks.work-note', $task) }}" class="admin-btn-outline">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty">No tasks assigned yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)]">
        <div class="admin-panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product management</p>
                    <h2 class="mt-2 text-3xl font-semibold">Products</h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="admin-search">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.03 4.11l3.43 3.43a.75.75 0 1 1-1.06 1.06l-3.43-3.43A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                        </svg>
                        <input type="text" placeholder="Search product..." class="admin-input">
                    </div>
                    <a href="{{ route('admin.categories.create') }}" class="admin-btn-outline">
                        Add Category
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">
                        Add New Product
                    </a>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="admin-pill {{ request('category') ? '' : 'admin-pill-active' }}">
                    All products
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('admin.dashboard', ['category' => $category->id]) }}"
                       class="admin-pill {{ (string) request('category') === (string) $category->id ? 'admin-pill-active' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
                <a href="{{ route('admin.categories.create') }}" class="admin-pill">
                    Add Category
                </a>
                <div class="ml-auto">
                    <button class="admin-pill">Sort by</button>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    @php
                        $categoryNames = $product->categories->pluck('name')->filter()->values();
                        if ($categoryNames->isEmpty() && $product->category?->name) {
                            $categoryNames = collect([$product->category->name]);
                        }
                    @endphp
                    <div class="admin-product-card">
                        <div class="admin-product-media">
                            <div class="admin-product-image">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full rounded-[18px] object-cover">
                                @else
                                    <span class="admin-product-tag">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $categoryNames->isNotEmpty() ? $categoryNames->join(', ') : 'Uncategorized' }}</p>
                            <p class="mt-3 text-sm font-semibold text-slate-900">KSh {{ number_format($product->price, 2) }}</p>
                            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                <span>Stock {{ $product->stock }}</span>
                                <span class="admin-status {{ $product->status === 'active' ? 'admin-status-active' : 'admin-status-draft' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </div>
                            <a href="{{ route('admin.products.edit', $product) }}" class="mt-3 inline-flex text-xs font-semibold text-brand underline underline-offset-4">
                                Edit product
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty sm:col-span-2 xl:col-span-3">
                        No products yet. Add your first product to populate the grid.
                    </div>
                @endforelse
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm text-slate-500">
                <span>Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</span>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-2">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <div class="admin-panel p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Edit products</h3>
                    <p class="text-xs text-slate-500">Update the highlighted product.</p>
                </div>
                @if ($featuredProduct)
                    <a href="{{ route('admin.products.edit', $featuredProduct) }}" class="text-xs font-semibold text-brand underline underline-offset-4">
                        See full view
                    </a>
                @endif
            </div>

            <div class="mt-4 flex gap-2">
                <button class="admin-tab admin-tab-active">Descriptions</button>
                <button class="admin-tab">Inventory</button>
                <button class="admin-tab">Pricing</button>
            </div>

            @if ($featuredProduct)
                <div class="mt-6 admin-product-media">
                    <div class="admin-product-image admin-product-image-lg">
                        @if ($featuredProduct->image_url)
                            <img src="{{ $featuredProduct->image_url }}" alt="{{ $featuredProduct->name }}" class="h-full w-full rounded-[18px] object-cover">
                        @else
                            <span class="admin-product-tag">{{ strtoupper(substr($featuredProduct->name, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.products.update', $featuredProduct) }}" class="mt-6 space-y-5" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="sync_relations" value="0">
                    <input type="hidden" name="discount_type" value="{{ $featuredProduct->discount_type }}">
                    <input type="hidden" name="discount_value" value="{{ $featuredProduct->discount_value }}">

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product name</label>
                        <input type="text" name="name" value="{{ old('name', $featuredProduct->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Description</label>
                        <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('description', $featuredProduct->description) }}</textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Category</label>
                            <select name="category_id" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $featuredProduct->category_id) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Brand</label>
                            <select name="brand_id" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">No brand</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $featuredProduct->brand_id) == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Price (KSh)</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $featuredProduct->price) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Stock</label>
                            <input type="number" min="0" name="stock" value="{{ old('stock', $featuredProduct->stock) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product image</label>
                        <input type="file" name="image" accept="image/*" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Status</label>
                            <select name="status" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="draft" @selected(old('status', $featuredProduct->status) === 'draft')>Draft</option>
                                <option value="active" @selected(old('status', $featuredProduct->status) === 'active')>Active</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $featuredProduct->sku) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.products.edit', $featuredProduct) }}" class="admin-btn-outline">
                            Discard
                        </a>
                        <button type="submit" class="admin-btn-primary">
                            Update product
                        </button>
                    </div>
                </form>
            @else
                <div class="admin-empty mt-6">
                    No products to edit yet. Add a product to get started.
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('[data-performance-filter]');
            if (!form) {
                return;
            }

            const period = form.querySelector('[data-performance-period]');
            const customFields = form.querySelector('[data-performance-custom]');

            period?.addEventListener('change', () => {
                const isCustom = period.value === 'custom';
                customFields?.classList.toggle('hidden', !isCustom);
                customFields?.classList.toggle('flex', isCustom);

                if (!isCustom) {
                    form.submit();
                }
            });

            document.querySelectorAll('[data-finance-edit]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-finance-edit');
                    document.querySelector(`[data-finance-edit-row="${id}"]`)?.classList.remove('hidden');
                });
            });

            document.querySelectorAll('[data-finance-cancel]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-finance-cancel');
                    document.querySelector(`[data-finance-edit-row="${id}"]`)?.classList.add('hidden');
                });
            });
        })();
    </script>
@endpush
