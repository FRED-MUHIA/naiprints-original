<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_id',
        'type',
        'phone',
        'amount',
        'reference',
        'checkout_request_id',
        'merchant_request_id',
        'receipt_number',
        'status',
        'result_code',
        'result_desc',
        'raw_request',
        'raw_callback',
        'occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_request' => 'array',
        'raw_callback' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
