@php
    $assignedAtValue = old('assigned_at');
    if ($assignedAtValue === null) {
        $assignedAtValue = optional($task->assigned_at)->format('Y-m-d\TH:i');
    }

    $totalStagesPreview = (int) old('total_stages', $task->total_stages ?? 3);
    $totalStagesPreview = max(1, min(20, $totalStagesPreview));
    $currentStagePreview = (int) old('current_stage', $task->current_stage ?? 1);
    $currentStagePreview = max(1, min($totalStagesPreview, $currentStagePreview));
    $statusPreview = old('status', $task->status ?? 'assigned');
    $currentStageProgressPreview = (int) old('current_stage_progress', $task->current_stage_progress ?? 0);
    $currentStageProgressPreview = max(0, min(100, $currentStageProgressPreview));
@endphp

<div class="admin-card space-y-5" data-task-stage-manager>
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

    <div class="grid gap-5 md:grid-cols-5">
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Status
            <select name="status" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-stage-status required>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $task->status ?? 'assigned') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="space-y-2 text-sm font-semibold text-slate-600">
            <label for="total_stages">Total Stages</label>
            <input id="total_stages" type="number" min="1" max="20" name="total_stages" value="{{ old('total_stages', $task->total_stages ?? 3) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-stage-total required>
            <div class="flex gap-2">
                <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400" data-stage-add>
                    + Add Stage
                </button>
                <button type="button" class="inline-flex items-center rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-400" data-stage-remove>
                    - Remove Stage
                </button>
            </div>
        </div>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Current Stage
            <input type="number" min="1" max="20" name="current_stage" value="{{ old('current_stage', $task->current_stage ?? 1) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-stage-current required>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Stage Progress (%)
            <input type="number" min="0" max="100" name="current_stage_progress" value="{{ old('current_stage_progress', $task->current_stage_progress ?? 0) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" data-stage-progress required>
        </label>
        <label class="space-y-2 text-sm font-semibold text-slate-600">
            Current Stage Label
            <input type="text" name="stage_label" value="{{ old('stage_label', $task->stage_label ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="Stage 1 - Design">
        </label>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>State</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody data-stage-preview-body>
                @for ($stageNo = 1; $stageNo <= $totalStagesPreview; $stageNo++)
                    @php
                        $isDone = $stageNo < $currentStagePreview
                            || (in_array($statusPreview, ['finished', 'delivered'], true) && $stageNo === $currentStagePreview);
                        $isCurrent = $stageNo === $currentStagePreview && !$isDone;
                        $state = $isDone ? 'Completed' : ($isCurrent ? 'Current' : 'Pending');
                        $stateClass = $isDone
                            ? 'bg-emerald-100 text-emerald-700'
                            : ($isCurrent ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700');
                        $stageProgress = $isDone ? 100 : ($isCurrent ? ($statusPreview === 'assigned' ? 0 : $currentStageProgressPreview) : 0);
                    @endphp
                    <tr>
                        <td>Stage {{ $stageNo }}</td>
                        <td><span class="admin-status {{ $stateClass }}">{{ $state }}</span></td>
                        <td>{{ $stageProgress }}%</td>
                    </tr>
                @endfor
            </tbody>
        </table>
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

@once
    @push('scripts')
        <script>
            (function () {
                const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

                const initStageManager = (scope) => {
                    const totalInput = scope.querySelector('[data-stage-total]');
                    const currentInput = scope.querySelector('[data-stage-current]');
                    const statusInput = scope.querySelector('[data-stage-status]');
                    const progressInput = scope.querySelector('[data-stage-progress]');
                    const addBtn = scope.querySelector('[data-stage-add]');
                    const removeBtn = scope.querySelector('[data-stage-remove]');
                    const previewBody = scope.querySelector('[data-stage-preview-body]');

                    if (!totalInput || !currentInput || !statusInput || !progressInput || !previewBody) {
                        return;
                    }

                    const render = () => {
                        let total = clamp(parseInt(totalInput.value || '1', 10) || 1, 1, 20);
                        let current = clamp(parseInt(currentInput.value || '1', 10) || 1, 1, total);
                        let currentProgress = clamp(parseInt(progressInput.value || '0', 10) || 0, 0, 100);
                        const status = statusInput.value || 'assigned';

                        totalInput.value = String(total);
                        currentInput.value = String(current);
                        progressInput.value = String(currentProgress);

                        previewBody.innerHTML = '';
                        for (let stageNo = 1; stageNo <= total; stageNo++) {
                            const done = stageNo < current || ((status === 'finished' || status === 'delivered') && stageNo === current);
                            const currentStage = stageNo === current && !done;
                            const state = done ? 'Completed' : (currentStage ? 'Current' : 'Pending');
                            const stateClass = done
                                ? 'bg-emerald-100 text-emerald-700'
                                : (currentStage ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700');
                            const stageProgress = done ? 100 : (currentStage ? (status === 'assigned' ? 0 : currentProgress) : 0);

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>Stage ${stageNo}</td>
                                <td><span class="admin-status ${stateClass}">${state}</span></td>
                                <td>${stageProgress}%</td>
                            `;
                            previewBody.appendChild(tr);
                        }
                    };

                    addBtn?.addEventListener('click', () => {
                        totalInput.value = String(clamp((parseInt(totalInput.value || '1', 10) || 1) + 1, 1, 20));
                        render();
                    });

                    removeBtn?.addEventListener('click', () => {
                        totalInput.value = String(clamp((parseInt(totalInput.value || '1', 10) || 1) - 1, 1, 20));
                        render();
                    });

                    [totalInput, currentInput, statusInput, progressInput].forEach((input) => {
                        input.addEventListener('input', render);
                        input.addEventListener('change', render);
                    });

                    render();
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        document.querySelectorAll('[data-task-stage-manager]').forEach(initStageManager);
                    });
                } else {
                    document.querySelectorAll('[data-task-stage-manager]').forEach(initStageManager);
                }
            })();
        </script>
    @endpush
@endonce
