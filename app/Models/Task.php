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
        'stage_label',
        'finished_at',
        'delivered_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
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

        return (int) round(($current / $total) * 100);
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
