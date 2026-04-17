<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ trim($__env->yieldContent('email_title', $brandName ?? 'Nai Prints')) }}</title>
    </head>
    <body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
        @php
            $brandName = $brandName ?? config('mail.from.name', config('app.name', 'Nai Prints'));
            $supportEmail = $supportEmail ?? config('mail.support.address', config('mail.from.address', ''));
            $supportPhone = $supportPhone ?? '';
            $supportWebsite = $supportWebsite ?? config('app.url');
            $preheader = trim($__env->yieldContent('preheader'));
            $heroEyebrow = trim($__env->yieldContent('hero_eyebrow'));
            $heroTitle = trim($__env->yieldContent('hero_title'));
            $heroSubtitle = trim($__env->yieldContent('hero_subtitle'));
            $footerNote = trim($__env->yieldContent('footer_note'));
        @endphp

        @if ($preheader !== '')
            <span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;mso-hide:all;">
                {{ $preheader }}
            </span>
        @endif

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;background:#eef2f7;">
            <tr>
                <td align="center">
                    <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:100%;max-width:640px;background:#f8fafc;border:1px solid #d9e2ec;border-radius:16px;overflow:hidden;">
                        <tr>
                            <td style="padding:20px 24px 10px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="vertical-align:middle;">
                                            <table role="presentation" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td style="width:30px;height:30px;border-radius:8px;background:#0f172a;color:#ffffff;text-align:center;font-size:12px;font-weight:700;vertical-align:middle;">
                                                        NP
                                                    </td>
                                                    <td style="padding-left:10px;font-size:24px;font-weight:700;color:#111827;vertical-align:middle;">
                                                        {{ $brandName }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td align="right" style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#64748b;vertical-align:middle;">
                                            {{ $heroEyebrow !== '' ? $heroEyebrow : 'Client Update' }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 24px;">
                                <div style="border-radius:14px;padding:34px 28px;background:#0b1020;background-image:radial-gradient(circle at 85% 10%, rgba(34,211,238,0.42), transparent 50%), radial-gradient(circle at 10% 95%, rgba(59,130,246,0.5), transparent 52%), linear-gradient(125deg, #05070f 18%, #0b1e4e 58%, #0f718f 100%);">
                                    <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#a5f3fc;">
                                        {{ $heroEyebrow !== '' ? $heroEyebrow : 'Nai Prints' }}
                                    </p>
                                    <h1 style="margin:0 0 12px;font-size:40px;line-height:1.06;letter-spacing:-0.6px;color:#ffffff;font-weight:700;">
                                        {{ $heroTitle !== '' ? $heroTitle : 'Your update is ready' }}
                                    </h1>
                                    @if ($heroSubtitle !== '')
                                        <p style="margin:0;font-size:17px;line-height:1.45;color:#e2e8f0;max-width:400px;">
                                            {{ $heroSubtitle }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px;">
                                @yield('content')
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 24px;">
                                <div style="height:1px;background:#d9e2ec;"></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:20px 24px 24px;">
                                <p style="margin:0 0 8px;font-size:20px;line-height:1.3;font-weight:700;color:#111827;">Need help with this update?</p>
                                @if ($supportEmail !== '' || $supportPhone !== '' || $supportWebsite !== '')
                                    <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;">
                                        @if ($supportEmail !== '')
                                            Email: <a href="mailto:{{ $supportEmail }}" style="color:#0f766e;text-decoration:none;">{{ $supportEmail }}</a>
                                        @endif
                                        @if ($supportPhone !== '')
                                            @if ($supportEmail !== '')
                                                <span style="color:#cbd5e1;"> | </span>
                                            @endif
                                            Phone: {{ $supportPhone }}
                                        @endif
                                        @if ($supportWebsite !== '')
                                            @if ($supportEmail !== '' || $supportPhone !== '')
                                                <span style="color:#cbd5e1;"> | </span>
                                            @endif
                                            <a href="{{ $supportWebsite }}" style="color:#0f766e;text-decoration:none;">Visit our website</a>
                                        @endif
                                    </p>
                                @endif
                                @if ($footerNote !== '')
                                    <p style="margin:10px 0 0;font-size:12px;line-height:1.5;color:#94a3b8;">{{ $footerNote }}</p>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
