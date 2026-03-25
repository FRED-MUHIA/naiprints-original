@php
    $brand = $support['brand'] ?? 'Nai Prints';
    $logoUrl = $support['logo_url'] ?? '';
    $supportEmail = $support['email'] ?? '';
    $supportPhone = $support['phone'] ?? '';
    $items = $order['items'] ?? [];
    $invoiceUrl = $order['invoice_url'] ?? '';
@endphp
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $brand }} Order Confirmation</title>
    </head>
    <body style="margin:0;padding:0;background:#f8fafc;font-family:Arial, sans-serif;color:#0f172a;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc;padding:24px 0;">
            <tr>
                <td align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e2e8f0;">
                        <tr>
                            <td style="padding:24px 28px;background:#0f172a;color:#ffffff;">
                                <table role="presentation" width="100%">
                                    <tr>
                                        <td>
                                            <p style="margin:0;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#94a3b8;">Order Confirmation</p>
                                            <h1 style="margin:8px 0 0;font-size:24px;">Thanks for your order, {{ $order['customer']['name'] ?? 'Customer' }}.</h1>
                                        </td>
                                        <td align="right">
                                            @if ($logoUrl)
                                                <img src="{{ $logoUrl }}" alt="{{ $brand }} logo" width="48" height="48" style="border-radius:12px;background:#ffffff;padding:6px;">
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px 28px;">
                                <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Order number</p>
                                <p style="margin:0 0 18px;font-size:18px;font-weight:700;">{{ $order['number'] ?? '' }}</p>
                                @if ($invoiceUrl)
                                    <p style="margin:0 0 18px;">
                                        <a href="{{ $invoiceUrl }}" style="display:inline-block;padding:10px 14px;border-radius:999px;background:#e11d2e;color:#ffffff;text-decoration:none;font-size:13px;">
                                            View invoice
                                        </a>
                                    </p>
                                @endif

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-weight:700;">Item</td>
                                        <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-weight:700;" align="center">Qty</td>
                                        <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-weight:700;" align="right">Total</td>
                                    </tr>
                                    @foreach ($items as $item)
                                        @php
                                            $lineTotal = ($item['price'] ?? 0) * ($item['qty'] ?? 0);
                                            $variant = collect([$item['color'] ?? '', $item['size'] ?? ''])->filter()->implode(' / ');
                                        @endphp
                                        <tr>
                                            <td style="padding:12px 0;border-bottom:1px solid #f1f5f9;">
                                                <div style="font-weight:600;">{{ $item['name'] ?? 'Product' }}</div>
                                                @if ($variant)
                                                    <div style="font-size:12px;color:#94a3b8;">{{ $variant }}</div>
                                                @endif
                                            </td>
                                            <td style="padding:12px 0;border-bottom:1px solid #f1f5f9;" align="center">{{ $item['qty'] ?? 0 }}</td>
                                            <td style="padding:12px 0;border-bottom:1px solid #f1f5f9;" align="right">KSh {{ number_format($lineTotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;">
                                    <tr>
                                        <td style="padding:6px 0;color:#64748b;">Subtotal</td>
                                        <td style="padding:6px 0;" align="right">KSh {{ number_format($order['subtotal'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:#64748b;">Delivery</td>
                                        <td style="padding:6px 0;" align="right">Calculated at checkout</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:10px 0;border-top:1px solid #e2e8f0;font-weight:700;">Total</td>
                                        <td style="padding:10px 0;border-top:1px solid #e2e8f0;font-weight:700;" align="right">KSh {{ number_format($order['total'] ?? 0, 2) }}</td>
                                    </tr>
                                </table>

                                <div style="margin-top:24px;padding:16px;border-radius:16px;background:#f8fafc;">
                                    <p style="margin:0 0 6px;font-weight:700;">Delivery details</p>
                                    <p style="margin:0;font-size:14px;color:#475569;">{{ $order['shipping']['address'] ?? '' }}, {{ $order['shipping']['city'] ?? '' }}</p>
                                    @if (!empty($order['shipping']['notes']))
                                        <p style="margin:6px 0 0;font-size:13px;color:#64748b;">Notes: {{ $order['shipping']['notes'] }}</p>
                                    @endif
                                </div>

                                <div style="margin-top:20px;padding:16px;border-radius:16px;border:1px dashed #e2e8f0;">
                                    <p style="margin:0 0 6px;font-weight:700;">Contact us</p>
                                    <p style="margin:0;font-size:14px;color:#475569;">{{ $brand }}</p>
                                    <p style="margin:4px 0 0;font-size:13px;color:#64748b;">Email: {{ $supportEmail }}</p>
                                    <p style="margin:4px 0 0;font-size:13px;color:#64748b;">Phone: {{ $supportPhone }}</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:18px 28px;background:#f8fafc;color:#94a3b8;font-size:12px;text-align:center;">
                                You are receiving this email because you placed an order on {{ $brand }}.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
