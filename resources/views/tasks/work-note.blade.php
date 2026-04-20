<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $task->reference }} Work Note</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; font-size: 12px; color: #1f2937; }
        .page { padding: 28px 32px; }
        .topbar { height: 6px; background: #e11d2e; margin: -28px -32px 18px -32px; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .header td { vertical-align: top; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 20px; text-transform: uppercase; color: #e11d2e; letter-spacing: 1px; }
        .doc-meta { margin-top: 6px; font-size: 11px; color: #6b7280; }
        .panel-grid { width: 100%; border-collapse: separate; border-spacing: 12px; margin-top: 8px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; background: #f9fafb; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1.8px; color: #6b7280; margin-bottom: 6px; }
        .block { margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; }
        .block p { margin: 0; white-space: pre-line; }
        .muted { color: #6b7280; }
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #e2e8f0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .timeline { margin-top: 16px; border-top: 1px dashed #cbd5e1; padding-top: 12px; }
        .timeline-item { margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar"></div>
        <table class="header">
            <tr>
                <td style="width: 60%;">
                    <h2 style="margin: 0; font-size: 20px;">Nai Prints</h2>
                    <div class="muted">Task Production Work Note</div>
                </td>
                <td style="width: 40%;">
                    <div class="doc-title">
                        <h1>Work Note</h1>
                        <div class="doc-meta">Reference: {{ $task->reference }}</div>
                        <div class="doc-meta">Generated: {{ now()->format('M d, Y h:i A') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="panel-grid">
            <tr>
                <td class="panel" style="width: 50%;">
                    <div class="section-title">Client</div>
                    <div><strong>{{ $task->client?->name ?? '-' }}</strong></div>
                    <div>{{ $task->client?->company_name }}</div>
                    <div>{{ $task->client?->phone }}</div>
                    <div>{{ $task->client?->email }}</div>
                </td>
                <td class="panel" style="width: 50%;">
                    <div class="section-title">Assignment</div>
                    <div><strong>Task:</strong> {{ $task->title }}</div>
                    <div><strong>Assigned to:</strong> {{ $task->assigned_to ?? '-' }}</div>
                    <div><strong>Assigned time:</strong> {{ optional($task->assigned_at)->format('M d, Y h:i A') ?? '-' }}</div>
                    <div style="margin-top: 8px;">
                        <span class="status">{{ $task->status_label }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="block">
            <div class="section-title">Progress</div>
            <p>
Stage {{ $task->current_stage }}/{{ $task->total_stages }} ({{ $task->progress_percent }}%)
Current stage progress: {{ (int) $task->current_stage_progress }}%
{{ $task->stage_label ? "Current stage detail: {$task->stage_label}" : '' }}
            </p>
        </div>

        <div class="block">
            <div class="section-title">Requirements</div>
            <p>{{ $task->requirement ?: 'No explicit requirements added.' }}</p>
        </div>

        <div class="block">
            <div class="section-title">Task Details</div>
            <p>{{ $task->task_details }}</p>
        </div>

        <div class="timeline">
            <div class="section-title">Timeline</div>
            <div class="timeline-item"><strong>Assigned:</strong> {{ optional($task->assigned_at)->format('M d, Y h:i A') ?? '-' }}</div>
            @if ($task->finished_at)
                <div class="timeline-item"><strong>Finished:</strong> {{ optional($task->finished_at)->format('M d, Y h:i A') }}</div>
            @endif
            @if ($task->delivered_at)
                <div class="timeline-item"><strong>Delivered to client:</strong> {{ optional($task->delivered_at)->format('M d, Y h:i A') }}</div>
            @endif
        </div>
    </div>
</body>
</html>
