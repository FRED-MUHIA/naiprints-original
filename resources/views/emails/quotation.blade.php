@php
    $settings = \App\Models\InvoiceSetting::query()->first();
    $brandName = $settings?->business_name ?: config('mail.from.name', config('app.name', 'Nai Prints'));
    $logoUrl = $settings?->logo_path
        ? url(\Illuminate\Support\Facades\Storage::url($settings->logo_path))
        : url('/images/logo.svg');
    $supportEmail = $settings?->email ?: \App\Models\InvoiceSetting::DEFAULT_EMAIL;
    $supportPhone = $settings?->phone ?: \App\Models\InvoiceSetting::DEFAULT_PHONE;
    $supportLocation = \App\Models\InvoiceSetting::DEFAULT_LOCATION;
    $supportWebsite = $settings?->website ?: config('app.url');
@endphp

@extends('emails.layouts.client')

@section('email_title', "Quotation {$quotation->number} from {$brandName}")
@section('preheader', "Quotation {$quotation->number} is ready. Total KES " . number_format((float) $quotation->total, 2))
@section('hero_eyebrow', 'Quotation Ready')
@section('hero_title', 'Your quotation is ready')
@section('hero_subtitle', 'We attached the PDF quotation to this email for your review and approval.')
@section('footer_note', 'Quotation terms and pricing are valid only within the stated validity period.')

@section('content')
    <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#1f2937;">
        Hello {{ $quotation->client?->name ?? 'there' }},
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#475569;">
        Quotation <strong>{{ $quotation->number }}</strong> is attached as a PDF. If you would like any changes, reply to this email and we will update it.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #dbe3ee;border-radius:12px;overflow:hidden;background:#ffffff;">
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Quotation Number</td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #eef2f7;">{{ $quotation->number }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Issue Date</td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #eef2f7;">{{ optional($quotation->issue_date)->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;">Valid Until</td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #eef2f7;">{{ optional($quotation->valid_until)->format('M d, Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;">Total Amount</td>
            <td align="right" style="padding:12px 14px;font-size:16px;color:#0f172a;font-weight:700;">KES {{ number_format((float) $quotation->total, 2) }}</td>
        </tr>
    </table>
@endsection
