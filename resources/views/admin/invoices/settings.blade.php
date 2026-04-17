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
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Signature Image
                    <input type="file" name="signature" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                    @if ($settings->signature_path)
                        <span class="text-xs text-slate-500">Current: {{ $settings->signature_path }}</span>
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
            <h2 class="text-lg font-semibold">M-Pesa API</h2>
            @php
                $mpesa = $settings->mpesa_config ?? [];
            @endphp
            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Environment
                    <select name="mpesa[environment]" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                        <option value="">Select</option>
                        <option value="sandbox" @selected(old('mpesa.environment', $mpesa['environment'] ?? '') === 'sandbox')>Sandbox</option>
                        <option value="live" @selected(old('mpesa.environment', $mpesa['environment'] ?? '') === 'live')>Live</option>
                    </select>
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Shortcode
                    <input type="text" name="mpesa[shortcode]" value="{{ old('mpesa.shortcode', $mpesa['shortcode'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Consumer Key
                    <input type="text" name="mpesa[consumer_key]" value="{{ old('mpesa.consumer_key', $mpesa['consumer_key'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Consumer Secret
                    <input type="password" name="mpesa[consumer_secret]" value="{{ old('mpesa.consumer_secret', $mpesa['consumer_secret'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Passkey
                    <input type="password" name="mpesa[passkey]" value="{{ old('mpesa.passkey', $mpesa['passkey'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    STK Transaction Type
                    <select name="mpesa[transaction_type]" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                        <option value="">Select</option>
                        <option value="CustomerPayBillOnline" @selected(old('mpesa.transaction_type', $mpesa['transaction_type'] ?? '') === 'CustomerPayBillOnline')>Customer PayBill</option>
                        <option value="CustomerBuyGoodsOnline" @selected(old('mpesa.transaction_type', $mpesa['transaction_type'] ?? '') === 'CustomerBuyGoodsOnline')>Customer BuyGoods</option>
                    </select>
                </label>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 md:col-span-2">
                    Security Credential and Initiator are not required for STK Push/C2B in this system.
                    They are backend-only for advanced APIs (for example B2C) and are intentionally hidden.
                </div>
                <label class="space-y-2 text-sm font-semibold text-slate-600 md:col-span-2">
                    STK Callback URL
                    <input type="text" name="mpesa[callback_url]" value="{{ old('mpesa.callback_url', $mpesa['callback_url'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="{{ $callbackUrls['stk'] ?? '' }}">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600 md:col-span-2">
                    C2B Confirmation URL
                    <input type="text" name="mpesa[confirmation_url]" value="{{ old('mpesa.confirmation_url', $mpesa['confirmation_url'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="{{ $callbackUrls['c2b_confirmation'] ?? '' }}">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600 md:col-span-2">
                    C2B Validation URL
                    <input type="text" name="mpesa[validation_url]" value="{{ old('mpesa.validation_url', $mpesa['validation_url'] ?? '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" placeholder="{{ $callbackUrls['c2b_validation'] ?? '' }}">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    C2B Response Type
                    <select name="mpesa[response_type]" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                        <option value="">Select</option>
                        <option value="Completed" @selected(old('mpesa.response_type', $mpesa['response_type'] ?? '') === 'Completed')>Completed</option>
                        <option value="Canceled" @selected(old('mpesa.response_type', $mpesa['response_type'] ?? '') === 'Canceled')>Canceled</option>
                    </select>
                </label>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit"
                        class="admin-btn-outline"
                        formaction="{{ route('admin.invoices.mpesa.test') }}"
                        formmethod="POST">
                    Test M-Pesa Credentials
                </button>
                <button type="submit"
                        class="admin-btn-outline"
                        formaction="{{ route('admin.invoices.mpesa.register') }}"
                        formmethod="POST">
                    Register C2B URLs
                </button>
                <div class="text-xs text-slate-500">
                    Save settings first, then register C2B URLs. Use the invoice number as the account reference.
                </div>
            </div>
        </div>

        <div class="admin-card space-y-4">
            <h2 class="text-lg font-semibold">Footer</h2>
            <textarea name="footer_text" rows="3" class="admin-input border border-slate-200 rounded-xl px-4 py-2">{{ old('footer_text', $settings->footer_text) }}</textarea>
        </div>

        <div class="admin-card space-y-4">
            <h2 class="text-lg font-semibold">Bank Transfer Details</h2>
            <textarea name="bank_details"
                      rows="5"
                      class="admin-input border border-slate-200 rounded-xl px-4 py-2"
                      placeholder="Bank name, account name, account number, branch, etc.">{{ old('bank_details', $settings->bank_details) }}</textarea>
            <p class="text-xs text-slate-500">Leave blank to hide bank details.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Save Settings</button>
            <a href="{{ route('admin.invoices.index') }}" class="admin-btn-outline">Back</a>
        </div>
    </form>
@endsection
