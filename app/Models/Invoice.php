<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'affiliate_id',
        'affiliate_click_id',
        'affiliate_code',
        'sequence',
        'number',
        'public_id',
        'issue_date',
        'due_date',
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
        'due_date' => 'date',
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

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function affiliateClick()
    {
        return $this->belongsTo(AffiliateClick::class);
    }

    public function affiliateCommission()
    {
        return $this->hasOne(AffiliateCommission::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest('payment_date');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class)->latest('payment_date');
    }

    public function mpesaTransactions()
    {
        return $this->hasMany(MpesaTransaction::class)->latest();
    }

    public function getAmountPaidAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - $this->amount_paid);
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

    public function refreshStatus(): void
    {
        $paid = $this->amount_paid;
        $total = (float) $this->total;

        if ($paid <= 0) {
            $this->status = 'unpaid';
            return;
        }

        if ($paid + 0.01 < $total) {
            $this->status = 'partially_paid';
            return;
        }

        $this->status = 'paid';
    }
}
