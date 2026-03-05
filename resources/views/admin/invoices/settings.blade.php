@extends('admin.layout')

@section('title', 'Invoice Settings')

@section('content')
    <form method="POST" action="{{ route('admin.invoices.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="admin-card space-y-5">
            <h2 class="text-lg font-semibold">Branding</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Business Name
                    <input type="text" name="business_name" value="{{ old('business_name', $settings->business_name) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Theme Color
                    <input type="text" name="theme_color" value="{{ old('theme_color', $settings->theme_color) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Phone
                    <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Email
                    <input type="email" name="email" value="{{ old('email', $settings->email) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Website
                    <input type="text" name="website" value="{{ old('website', $settings->website) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Logo
                    <input type="file" name="logo" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                    @if ($settings->logo_path)
                        <span class="text-xs text-slate-500">Current: {{ $settings->logo_path }}</span>
                    @endif
                </label>
            </div>
        </div>

        <div class="admin-card space-y-4">
            <h2 class="text-lg font-semibold">Payment Methods</h2>
            <div class="grid gap-3 md:grid-cols-2">
                @php
                    $methods = old('payment_methods', $settings->payment_methods ?? []);
                    $methods = array_values(array_filter($methods));
                    $methods = array_pad($methods, 4, '');
                @endphp
                @foreach ($methods as $index => $method)
                    <input type="text" name="payment_methods[]" value="{{ $method }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="Payment method">
                @endforeach
            </div>
            <p class="text-xs text-slate-500">Add up to 4 methods (M-Pesa, Bank Transfer, Cash, etc.).</p>
        </div>

        <div class="admin-card space-y-4">
            <h2 class="text-lg font-semibold">Footer</h2>
            <textarea name="footer_text" rows="3" class="admin-input border border-slate-200 rounded-xl px-4 py-2">{{ old('footer_text', $settings->footer_text) }}</textarea>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Save Settings</button>
            <a href="{{ route('admin.invoices.index') }}" class="admin-btn-outline">Back</a>
        </div>
    </form>
@endsection
