@extends('emails.layouts.client')

@section('email_title', $subjectLine)
@section('preheader', $messageBody)
@section('hero_eyebrow', 'Affiliate Program')
@section('hero_title', $subjectLine)
@section('hero_subtitle', 'Nai Prints affiliate update')
@section('footer_note', 'You are receiving this email because you registered for the Nai Prints affiliate program.')

@section('content')
    <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#1f2937;">
        Hello {{ $affiliate?->full_name ?? 'there' }},
    </p>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#475569;">
        {{ $messageBody }}
    </p>
    @if ($affiliate?->isApproved())
        <p style="margin:0;">
            <a href="{{ route('affiliate.login') }}" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#e11d2e;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                Login To Affiliate Account
            </a>
        </p>
    @endif
@endsection
