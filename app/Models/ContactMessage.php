<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'source_url',
        'ip_address',
        'user_agent',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];
}
