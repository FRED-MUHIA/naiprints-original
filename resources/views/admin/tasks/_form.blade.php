@php
    $assignedAtValue = old('assigned_at');
    if ($assignedAtValue === null) {
        $assignedAtValue = optional($task->assigned_at)->format('Y-m-d\TH:i');
    }
@endphp

<div class="admin-card space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Client
            <select name="client_id" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                <option value="">Select client...</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) old('client_id', $task->client_id) === (string) $client->id)>
                        {{ $client->display_name }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Task Title
            <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Assigned To
            <input type="text" name="assigned_to" value="{{ old('assigned_to', $task->assigned_to ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="Production team / designer / staff">
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Assigned Time
            <input type="datetime-local" name="assigned_at" value="{{ $assignedAtValue }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
        </label>
    </div>

    <div class="grid gap-5 md:grid-cols-4">
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Status
            <select name="status" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $task->status ?? 'assigned') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Total Stages
            <input type="number" min="1" max="20" name="total_stages" value="{{ old('total_stages', $task->total_stages ?? 3) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Current Stage
            <input type="number" min="1" max="20" name="current_stage" value="{{ old('current_stage', $task->current_stage ?? 1) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Current Stage Label
            <input type="text" name="stage_label" value="{{ old('stage_label', $task->stage_label ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="Stage 1 - Design">
        </label>
    </div>

    <label class="space-y-2 text-sm font-semibold text-slate-600">
        Requirements
        <textarea name="requirement" rows="5" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="What must be delivered and quality requirements...">{{ old('requirement', $task->requirement ?? '') }}</textarea>
    </label>

    <label class="space-y-2 text-sm font-semibold text-slate-600">
        Task Details
        <textarea name="task_details" rows="6" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="Design notes, print specs, quantities, deadlines..." required>{{ old('task_details', $task->task_details ?? '') }}</textarea>
    </label>
</div>
