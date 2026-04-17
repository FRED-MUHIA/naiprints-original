@php
    $invoice = $receipt->invoice;
    $receiptLink = route('receipt.public', $receipt->public_id);
    $settings = \App\Models\InvoiceSetting::query()->first();
    $brandName = $settings?->business_name ?: config('mail.from.name', config('app.name', 'Nai Prints'));
    $supportEmail = $settings?->email ?: config('mail.support.address', config('mail.from.address', ''));
    $supportPhone = $settings?->phone ?: '';
    $supportWebsite = $settings?->website ?: config('app.url');
@endphp

@extends('emails.layouts.client')

@section('email_title', "Receipt {$receipt->number} from {$brandName}")
@section('preheader', "Payment received. Receipt {$receipt->number}, amount KES " . number_format((float) $receipt->amount, 2))
@section('hero_eyebrow', 'Payment Received')
@section('hero_title', 'Your receipt is ready')
@section('hero_subtitle', 'We have confirmed your payment and issued an official receipt.')
@section('footer_note', 'Keep this receipt for your accounting records.')

@section('content')
    <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#1f2937;">
        Hello {{ $invoice?->client?->name ?? 'there' }},
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#475569;">
        Thank you. We received your payment for invoice <strong>{{ $invoice?->number ?? '-' }}</strong>.
    </p>

    <p style="margin:0 0 22px;">
        <a href="{{ $receiptLink }}" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#e11d2e;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
            View Receipt
        </a>
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #dbe3ee;border-radius:12px;overflow:hidden;background:#ffffff;">
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Receipt Number</td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #eef2f7;">{{ $receipt->number }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Payment Date</td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #eef2f7;">{{ optional($receipt->payment_date)->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Amount Paid</td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #eef2f7;">KES {{ number_format((float) $receipt->amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;">Remaining Balance</td>
            <td align="right" style="padding:12px 14px;font-size:16px;color:#0f172a;font-weight:700;">KES {{ number_format((float) $receipt->balance_after, 2) }}</td>
        </tr>
    </table>
@endsection
