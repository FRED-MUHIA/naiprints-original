<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'sequence',
        'number',
        'issue_date',
        'valid_until',
        'status',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'terms',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('position');
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items->sum('total');
        $discount = (float) ($this->discount_amount ?? 0);
        $taxRate = $this->tax_rate !== null ? (float) $this->tax_rate : null;
        $taxable = max(0, $subtotal - $discount);
        $taxAmount = $taxRate !== null ? round(($taxRate / 100) * $taxable, 2) : 0;
        $total = $taxable + $taxAmount;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $total;
    }
}
