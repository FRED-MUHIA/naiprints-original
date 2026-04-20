@extends('admin.layout')

@section('title', 'Task Manager')

@section('content')
    <div class="grid gap-4 md:grid-cols-5">
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['total'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Assigned</p>
            <p class="mt-2 text-3xl font-semibold text-amber-700">{{ $stats['assigned'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">In Progress</p>
            <p class="mt-2 text-3xl font-semibold text-blue-700">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Finished</p>
            <p class="mt-2 text-3xl font-semibold text-teal-700">{{ $stats['finished'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Delivered</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $stats['delivered'] }}</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="admin-search">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search tasks..." class="admin-input">
                </div>
                <select name="status" class="admin-input border border-slate-200 rounded-xl px-3 py-2">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn-outline">Filter</button>
            </form>
            <a href="{{ route('admin.tasks.create') }}" class="admin-btn-primary">Assign Task</a>
        </div>
        <p class="mt-3 text-xs text-slate-500">
            `Next Stage` behavior: moves task to the next stage, marks final stage as `Finished`, and a final click marks it `Delivered`.
        </p>
    </div>

    <div class="admin-panel overflow-x-auto p-2">
        <table class="admin-table min-w-[1280px]">
            <thead>
                <tr>
                    <th class="px-5 py-3 whitespace-nowrap">Task</th>
                    <th class="px-5 py-3 whitespace-nowrap">Client</th>
                    <th class="px-5 py-3 whitespace-nowrap">Assigned To</th>
                    <th class="px-5 py-3 whitespace-nowrap">Assigned Time</th>
                    <th class="px-5 py-3 whitespace-nowrap">Progress</th>
                    <th class="px-5 py-3 whitespace-nowrap">Status</th>
                    <th class="px-5 py-3 whitespace-nowrap text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    @php
                        $statusClass = match ($task->status) {
                            'delivered' => 'bg-emerald-100 text-emerald-700',
                            'finished' => 'bg-teal-100 text-teal-700',
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <tr>
                        <td class="px-5 py-5 align-top font-semibold text-slate-700 break-words">{{ $task->reference }} - {{ $task->title }}</td>
                        <td class="px-5 py-5 align-top break-words">{{ $task->client?->display_name ?? '-' }}</td>
                        <td class="px-5 py-5 align-top whitespace-nowrap">{{ $task->assigned_to ?? '-' }}</td>
                        <td class="px-5 py-5 align-top whitespace-nowrap">{{ optional($task->assigned_at)->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-5 align-top">
                            <div class="w-[190px] space-y-1.5">
                                <div class="flex items-center gap-2 text-sm text-slate-700">
                                    <span class="font-medium">Stage {{ $task->current_stage }}/{{ $task->total_stages }}</span>
                                    <span class="text-xs text-slate-500">{{ $task->progress_percent }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full bg-brand" style="width: {{ $task->progress_percent }}%;"></div>
                                </div>
                                @if ($task->stage_label)
                                    <div class="text-xs text-slate-500 truncate">{{ $task->stage_label }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-5 align-top whitespace-nowrap">
                            <span class="admin-status {{ $statusClass }}">{{ $task->status_label }}</span>
                        </td>
                        <td class="px-5 py-5 align-top text-right">
                            <div class="ml-auto w-[260px] space-y-2">
                                @php
                                    $nextActionText = $task->current_stage < $task->total_stages
                                        ? 'Next Stage'
                                        : ($task->status === 'finished' ? 'Mark Delivered' : ($task->status === 'delivered' ? 'Delivered' : 'Mark Finished'));
                                @endphp
                                <form method="POST" action="{{ route('admin.tasks.next-stage', $task) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 whitespace-nowrap"
                                        @disabled($task->status === 'delivered')
                                    >
                                        {{ $nextActionText }}
                                    </button>
                                </form>
                                <div class="grid grid-cols-3 gap-2">
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-2.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 whitespace-nowrap">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.tasks.work-note', $task) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-2.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 whitespace-nowrap">
                                        PDF
                                    </a>
                                    <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-rose-300 px-2.5 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-400 whitespace-nowrap">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="admin-empty">No tasks yet. Assign your first task.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tasks->links() }}
@endsection
