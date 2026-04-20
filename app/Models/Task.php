<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'requirement',
        'task_details',
        'assigned_to',
        'assigned_at',
        'status',
        'total_stages',
        'current_stage',
        'current_stage_progress',
        'stage_label',
        'finished_at',
        'delivered_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'current_stage_progress' => 'integer',
        'finished_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getReferenceAttribute(): string
    {
        return 'TASK-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getProgressPercentAttribute(): int
    {
        $total = max(1, (int) $this->total_stages);
        $current = min($total, max(1, (int) $this->current_stage));
        $stageProgress = max(0, min(100, (int) $this->current_stage_progress));
        $completedStages = max(0, $current - 1);
        $overall = (($completedStages + ($stageProgress / 100)) / $total) * 100;

        return (int) max(0, min(100, round($overall)));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'finished' => 'Finished',
            'delivered' => 'Delivered',
            default => 'Assigned',
        };
    }
}
