<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function index(Request $request)
    {
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
        $task = new Task([
            'assigned_at' => now(),
            'status' => 'assigned',
            'total_stages' => 3,
            'current_stage' => 1,
        ]);
        $clients = Client::orderBy('name')->get();
        $statusOptions = $this->statusOptions();

        return view('admin.tasks.create', compact('task', 'clients', 'statusOptions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTask($request);
        $data = $this->normalizeTaskPayload($data);

        Task::create($data);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function edit(Task $task)
    {
        $clients = Client::orderBy('name')->get();
        $statusOptions = $this->statusOptions();

        return view('admin.tasks.edit', compact('task', 'clients', 'statusOptions'));
    }

    public function update(Request $request, Task $task)
    {
        $data = $this->validateTask($request);
        $data = $this->normalizeTaskPayload($data, $task);

        $task->update($data);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function downloadWorkNote(Task $task)
    {
        $task->load('client');
        $pdf = Pdf::loadView('tasks.work-note', compact('task'))
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("{$task->reference}-work-note.pdf");
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
            'stage_label' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function normalizeTaskPayload(array $data, ?Task $task = null): array
    {
        $totalStages = (int) $data['total_stages'];
        $currentStage = (int) $data['current_stage'];

        if ($currentStage > $totalStages) {
            throw ValidationException::withMessages([
                'current_stage' => 'Current stage cannot be greater than total stages.',
            ]);
        }

        if ($data['status'] === 'assigned' && $currentStage > 1) {
            $data['status'] = 'in_progress';
        }

        $existingFinishedAt = $task?->finished_at;
        $existingDeliveredAt = $task?->delivered_at;
        $now = now();

        if ($data['status'] === 'delivered') {
            $data['current_stage'] = $totalStages;
            $data['finished_at'] = $existingFinishedAt ?: $now;
            $data['delivered_at'] = $existingDeliveredAt ?: $now;
            return $data;
        }

        if ($data['status'] === 'finished') {
            $data['current_stage'] = $totalStages;
            $data['finished_at'] = $existingFinishedAt ?: $now;
            $data['delivered_at'] = null;
            return $data;
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
}
