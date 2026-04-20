<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $tasks = Task::with('client')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('assigned_to', 'like', "%{$search}%")
                        ->orWhere('stage_label', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('assigned_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Task::count(),
            'assigned' => Task::where('status', 'assigned')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'finished' => Task::where('status', 'finished')->count(),
            'delivered' => Task::where('status', 'delivered')->count(),
        ];
        $statusOptions = $this->statusOptions();

        return view('admin.tasks.index', compact('tasks', 'search', 'status', 'stats', 'statusOptions'));
    }

    public function create()
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $task = new Task([
            'assigned_at' => now(),
            'status' => 'assigned',
            'total_stages' => 3,
            'current_stage' => 1,
            'current_stage_progress' => 0,
        ]);
        $clients = Client::orderBy('name')->get();
        $statusOptions = $this->statusOptions();

        return view('admin.tasks.create', compact('task', 'clients', 'statusOptions'));
    }

    public function store(Request $request)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $data = $this->validateTask($request);
        $data = $this->normalizeTaskPayload($data);

        Task::create($data);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function edit($task)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $task = Task::findOrFail($task);
        $clients = Client::orderBy('name')->get();
        $statusOptions = $this->statusOptions();

        return view('admin.tasks.edit', compact('task', 'clients', 'statusOptions'));
    }

    public function update(Request $request, $task)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $task = Task::findOrFail($task);
        $data = $this->validateTask($request);
        $data = $this->normalizeTaskPayload($data, $task);

        $task->update($data);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy($task)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $task = Task::findOrFail($task);
        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function downloadWorkNote($task)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $task = Task::findOrFail($task);
        $task->load('client');
        $pdf = Pdf::loadView('tasks.work-note', compact('task'))
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("{$task->reference}-work-note.pdf");
    }

    public function nextStage($task)
    {
        if (!$this->isTaskModuleReady()) {
            return $this->missingTaskTableRedirect();
        }

        $task = Task::findOrFail($task);
        $now = now();
        $totalStages = max(1, (int) $task->total_stages);
        $currentStage = max(1, min($totalStages, (int) $task->current_stage));

        if ($task->status === 'delivered') {
            return back()->with('success', 'Task is already delivered.');
        }

        if ($currentStage < $totalStages) {
            $nextStage = $currentStage + 1;
            $task->update([
                'current_stage' => $nextStage,
                'current_stage_progress' => 0,
                'status' => 'in_progress',
                'finished_at' => null,
                'delivered_at' => null,
            ]);

            return back()->with('success', "Task moved to stage {$nextStage}.");
        }

        if ($task->status === 'finished') {
            $task->update([
                'status' => 'delivered',
                'current_stage_progress' => 100,
                'finished_at' => $task->finished_at ?: $now,
                'delivered_at' => $task->delivered_at ?: $now,
            ]);

            return back()->with('success', 'Task marked as delivered to the client.');
        }

        $task->update([
            'status' => 'finished',
            'current_stage_progress' => 100,
            'finished_at' => $task->finished_at ?: $now,
            'delivered_at' => null,
        ]);

        return back()->with('success', 'Task marked as finished. Click Next Stage again to mark delivered.');
    }

    private function validateTask(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'requirement' => ['nullable', 'string'],
            'task_details' => ['required', 'string'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'assigned_at' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statusOptions()))],
            'total_stages' => ['required', 'integer', 'min:1', 'max:20'],
            'current_stage' => ['required', 'integer', 'min:1', 'max:20'],
            'current_stage_progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stage_label' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function normalizeTaskPayload(array $data, ?Task $task = null): array
    {
        $totalStages = (int) $data['total_stages'];
        $currentStage = (int) $data['current_stage'];
        $currentStageProgress = max(0, min(100, (int) ($data['current_stage_progress'] ?? 0)));
        $data['current_stage_progress'] = $currentStageProgress;

        if ($currentStage > $totalStages) {
            throw ValidationException::withMessages([
                'current_stage' => 'Current stage cannot be greater than total stages.',
            ]);
        }

        if ($data['status'] === 'assigned' && ($currentStage > 1 || $currentStageProgress > 0)) {
            $data['status'] = 'in_progress';
        }

        $existingFinishedAt = $task?->finished_at;
        $existingDeliveredAt = $task?->delivered_at;
        $now = now();

        if ($data['status'] === 'delivered') {
            $data['current_stage'] = $totalStages;
            $data['current_stage_progress'] = 100;
            $data['finished_at'] = $existingFinishedAt ?: $now;
            $data['delivered_at'] = $existingDeliveredAt ?: $now;
            return $data;
        }

        if ($data['status'] === 'finished') {
            $data['current_stage'] = $totalStages;
            $data['current_stage_progress'] = 100;
            $data['finished_at'] = $existingFinishedAt ?: $now;
            $data['delivered_at'] = null;
            return $data;
        }

        if ($data['status'] === 'in_progress' && $currentStage === $totalStages && $currentStageProgress >= 100) {
            $data['status'] = 'finished';
            $data['current_stage_progress'] = 100;
            $data['finished_at'] = $existingFinishedAt ?: $now;
            $data['delivered_at'] = null;
            return $data;
        }

        if ($data['status'] === 'assigned') {
            $data['current_stage_progress'] = 0;
        }

        $data['finished_at'] = null;
        $data['delivered_at'] = null;

        return $data;
    }

    private function statusOptions(): array
    {
        return [
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'finished' => 'Finished',
            'delivered' => 'Delivered',
        ];
    }

    private function isTaskModuleReady(): bool
    {
        return Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'current_stage_progress');
    }

    private function missingTaskTableRedirect()
    {
        return redirect()->route('admin.dashboard')->withErrors([
            'tasks' => 'Task module is not ready yet. Run: php artisan migrate',
        ]);
    }
}
