<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class InvoiceSettingController extends Controller
{
    public function edit()
    {
        $settings = InvoiceSetting::first();
        if (!$settings) {
            $settings = InvoiceSetting::create(InvoiceSetting::defaults());
        }

        $callbackUrls = [
            'stk' => secure_url('/mpesa/stk/callback'),
            'c2b_confirmation' => secure_url('/mpesa/c2b/confirmation'),
            'c2b_validation' => secure_url('/mpesa/c2b/validation'),
        ];

        return view('admin.invoices.settings', compact('settings', 'callbackUrls'));
    }

    public function update(Request $request)
    {
        $settings = InvoiceSetting::first();
        if (!$settings) {
            $settings = InvoiceSetting::create(InvoiceSetting::defaults());
        }

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['nullable', 'string', 'max:20'],
            'footer_text' => ['nullable', 'string'],
            'bank_details' => ['nullable', 'string'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'signature' => ['nullable', 'image', 'max:4096'],
            'mpesa.environment' => ['nullable', 'in:sandbox,live'],
            'mpesa.consumer_key' => ['nullable', 'string', 'max:255'],
            'mpesa.consumer_secret' => ['nullable', 'string', 'max:255'],
            'mpesa.shortcode' => ['nullable', 'string', 'max:255'],
            'mpesa.passkey' => ['nullable', 'string', 'max:255'],
            'mpesa.callback_url' => ['nullable', 'string', 'max:255'],
            'mpesa.confirmation_url' => ['nullable', 'string', 'max:255'],
            'mpesa.validation_url' => ['nullable', 'string', 'max:255'],
            'mpesa.response_type' => ['nullable', 'in:Completed,Canceled'],
            'mpesa.transaction_type' => ['nullable', 'in:CustomerPayBillOnline,CustomerBuyGoodsOnline'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('invoices', 'public');
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $path;
        }
        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('invoices/signatures', 'public');
            if ($settings->signature_path) {
                Storage::disk('public')->delete($settings->signature_path);
            }
            $data['signature_path'] = $path;
        }

        $data['payment_methods'] = array_values(array_filter($data['payment_methods'] ?? []));
        $mpesaConfig = is_array($settings->mpesa_config) ? $settings->mpesa_config : [];
        foreach (($data['mpesa'] ?? []) as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                $placeholder = strtolower($value);
                if (in_array($placeholder, ['undefined', 'null', 'nil', 'none', 'n/a', 'na'], true)) {
                    $value = '';
                }
            }
            if ($value === null || $value === '') {
                unset($mpesaConfig[$key]);
                continue;
            }
            if (in_array($key, ['shortcode', 'party_b', 'till_number'], true) && is_string($value)) {
                $value = preg_replace('/\D+/', '', $value);
            }
            $mpesaConfig[$key] = $value;
        }
        $data['mpesa_config'] = $mpesaConfig;
        unset($data['mpesa']);

        $settings->update($data);
        Cache::forget('mpesa_token_sandbox');
        Cache::forget('mpesa_token_live');

        return redirect()->route('admin.invoices.settings')
            ->with('success', 'Invoice settings updated.');
    }

}
