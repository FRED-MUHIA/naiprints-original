<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'notes',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name) {
            return "{$this->name} ({$this->company_name})";
        }

        return $this->name;
    }
}
