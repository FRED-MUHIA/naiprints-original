<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ trim($__env->yieldContent('email_title', $brandName ?? 'Nai Prints')) }}</title>
        <style>
            @media only screen and (max-width: 680px) {
                .email-shell {
                    width: 100% !important;
                    max-width: 100% !important;
                }
                .email-pad {
                    padding: 16px !important;
                }
                .hero-wrap {
                    padding: 24px 18px !important;
                }
                .hero-title {
                    font-size: 30px !important;
                    line-height: 1.1 !important;
                }
                .hero-subtitle {
                    font-size: 15px !important;
                    max-width: none !important;
                }
            }
        </style>
    </head>
    <body style="margin:0;padding:0;background:#f1f3f5;font-family:Arial,Helvetica,sans-serif;color:#111827;">
        @php
            $brandName = $brandName ?? config('mail.from.name', config('app.name', 'Nai Prints'));
            $logoUrl = trim((string) ($logoUrl ?? url('/images/logo.svg')));
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

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;background:#f1f3f5;">
            <tr>
                <td align="center">
                    <table role="presentation" width="760" cellspacing="0" cellpadding="0" class="email-shell" style="width:100%;max-width:760px;background:#ffffff;border:1px solid #d1d5db;border-radius:16px;overflow:hidden;">
                        <tr>
                            <td class="email-pad" style="padding:20px 24px 10px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="vertical-align:middle;">
                                            <table role="presentation" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td style="width:50px;height:50px;vertical-align:middle;">
                                                        @if ($logoUrl !== '')
                                                            <img src="{{ $logoUrl }}"
                                                                 alt="{{ $brandName }} logo"
                                                                 width="50"
                                                                 height="50"
                                                                 style="display:block;width:50px;height:50px;border-radius:12px;border:1px solid #e5e7eb;background:#ffffff;object-fit:contain;padding:4px;">
                                                        @else
                                                            <div style="width:50px;height:50px;border-radius:12px;background:#111111;color:#ffffff;text-align:center;font-size:18px;font-weight:700;line-height:50px;">
                                                                NP
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td style="padding-left:10px;font-size:24px;font-weight:700;color:#111827;vertical-align:middle;">
                                                        {{ $brandName }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="email-pad" style="padding:0 24px;">
                                <div class="hero-wrap" style="border-radius:14px;padding:34px 28px;background:#12090b;background-image:radial-gradient(circle at 85% 8%, rgba(225,29,46,0.32), transparent 50%), radial-gradient(circle at 6% 95%, rgba(153,27,27,0.45), transparent 48%), linear-gradient(125deg, #050505 20%, #1a1a1a 58%, #3f0a10 100%);">
                                    <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#fecaca;">
                                        {{ $heroEyebrow !== '' ? $heroEyebrow : 'Nai Prints' }}
                                    </p>
                                    <h1 class="hero-title" style="margin:0 0 12px;font-size:42px;line-height:1.06;letter-spacing:-0.6px;color:#ffffff;font-weight:700;">
                                        {{ $heroTitle !== '' ? $heroTitle : 'Your update is ready' }}
                                    </h1>
                                    @if ($heroSubtitle !== '')
                                        <p class="hero-subtitle" style="margin:0;font-size:17px;line-height:1.45;color:#f3f4f6;max-width:460px;">
                                            {{ $heroSubtitle }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="email-pad" style="padding:24px;">
                                @yield('content')
                            </td>
                        </tr>
                        <tr>
                            <td class="email-pad" style="padding:0 24px;">
                                <div style="height:1px;background:#e5e7eb;"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="email-pad" style="padding:20px 24px 24px;">
                                <p style="margin:0 0 8px;font-size:20px;line-height:1.3;font-weight:700;color:#111827;">Need help with this update?</p>
                                @if ($supportEmail !== '' || $supportPhone !== '' || $supportWebsite !== '')
                                    <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;">
                                        @if ($supportEmail !== '')
                                            Email: <a href="mailto:{{ $supportEmail }}" style="color:#e11d2e;text-decoration:none;">{{ $supportEmail }}</a>
                                        @endif
                                        @if ($supportPhone !== '')
                                            @if ($supportEmail !== '')
                                                <span style="color:#d1d5db;"> | </span>
                                            @endif
                                            Phone: {{ $supportPhone }}
                                        @endif
                                        @if ($supportWebsite !== '')
                                            @if ($supportEmail !== '' || $supportPhone !== '')
                                                <span style="color:#d1d5db;"> | </span>
                                            @endif
                                            <a href="{{ $supportWebsite }}" style="color:#e11d2e;text-decoration:none;">Visit our website</a>
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
