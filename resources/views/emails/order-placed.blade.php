@php
    $orderNumber = $order['number'] ?? '';
    $brandName = $support['brand'] ?? config('mail.from.name', config('app.name', 'Nai Prints'));
    $logoUrl = $support['logo_url'] ?? url('/images/logo.svg');
    $supportEmail = $support['email'] ?? config('mail.support.address', config('mail.from.address', ''));
    $supportPhone = $support['phone'] ?? '';
    $supportWebsite = config('app.url');
    $items = $order['items'] ?? [];
    $invoiceUrl = $order['invoice_url'] ?? '';
@endphp

@extends('emails.layouts.client')

@section('email_title', "Order {$orderNumber} confirmation from {$brandName}")
@section('preheader', "Order {$orderNumber} confirmed. Total KES " . number_format((float) ($order['total'] ?? 0), 2))
@section('hero_eyebrow', 'Order Update')
@section('hero_title', 'Your order is confirmed')
@section('hero_subtitle', 'We are preparing your items and will share delivery progress updates.')
@section('footer_note', 'You are receiving this email because you placed an order on ' . $brandName . '.')

@section('content')
    <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#1f2937;">
        Hello {{ $order['customer']['name'] ?? 'Customer' }},
    </p>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#475569;">
        Thank you for your order. We have started processing <strong>{{ $order['number'] ?? '' }}</strong>.
    </p>

    @if ($invoiceUrl)
        <p style="margin:0 0 22px;">
            <a href="{{ $invoiceUrl }}" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#e11d2e;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                View Invoice
            </a>
        </p>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #dbe3ee;border-radius:12px;overflow:hidden;background:#ffffff;">
        <tr>
            <td style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;border-bottom:1px solid #eef2f7;">Item</td>
            <td align="center" style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;border-bottom:1px solid #eef2f7;">Qty</td>
            <td align="right" style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;border-bottom:1px solid #eef2f7;">Total</td>
        </tr>
        @foreach ($items as $item)
            @php
                $lineTotal = (float) ($item['price'] ?? 0) * (float) ($item['qty'] ?? 0);
                $variant = collect([
                    $item['selection'] ?? '',
                    $item['color'] ?? '',
                    $item['size'] ?? '',
                    !empty($item['custom_note'] ?? '') ? 'Note: ' . $item['custom_note'] : '',
                    !empty($item['custom_image'] ?? '') ? 'Customer image uploaded' : '',
                ])->filter()->implode(' / ');
            @endphp
            <tr>
                <td style="padding:12px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #eef2f7;">
                    <div style="font-weight:700;">{{ $item['name'] ?? 'Product' }}</div>
                    @if ($variant)
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $variant }}</div>
                    @endif
                    @if (!empty($item['custom_image'] ?? ''))
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">
                            Image: <a href="{{ $item['custom_image'] }}" style="color:#e11d2e;">View upload</a>
                        </div>
                    @endif
                </td>
                <td align="center" style="padding:12px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #eef2f7;">{{ $item['qty'] ?? 0 }}</td>
                <td align="right" style="padding:12px 14px;font-size:13px;color:#1e293b;border-bottom:1px solid #eef2f7;">KES {{ number_format($lineTotal, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:12px 14px;font-size:13px;color:#64748b;">Subtotal</td>
            <td></td>
            <td align="right" style="padding:12px 14px;font-size:13px;color:#0f172a;font-weight:700;">KES {{ number_format((float) ($order['subtotal'] ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td style="padding:0 14px 12px;font-size:13px;color:#64748b;">Total</td>
            <td></td>
            <td align="right" style="padding:0 14px 12px;font-size:16px;color:#0f172a;font-weight:700;">KES {{ number_format((float) ($order['total'] ?? 0), 2) }}</td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:14px;border:1px solid #dbe3ee;border-radius:12px;overflow:hidden;background:#f8fafc;">
        <tr>
            <td style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;">Delivery Details</td>
        </tr>
        <tr>
            <td style="padding:0 14px 12px;font-size:13px;line-height:1.6;color:#475569;">
                {{ $order['shipping']['address'] ?? '' }}, {{ $order['shipping']['city'] ?? '' }}
                @if (!empty($order['shipping']['notes']))
                    <br>Notes: {{ $order['shipping']['notes'] }}
                @endif
            </td>
        </tr>
    </table>
@endsection
