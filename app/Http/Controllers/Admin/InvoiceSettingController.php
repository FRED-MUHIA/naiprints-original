<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceSettingController extends Controller
{
    public function edit()
    {
        $settings = InvoiceSetting::first();
        if (!$settings) {
            $settings = InvoiceSetting::create(InvoiceSetting::defaults());
        }

        return view('admin.invoices.settings', compact('settings'));
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
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('invoices', 'public');
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $path;
        }

        $data['payment_methods'] = array_values(array_filter($data['payment_methods'] ?? []));

        $settings->update($data);

        return redirect()->route('admin.invoices.settings')
            ->with('success', 'Invoice settings updated.');
    }
}
