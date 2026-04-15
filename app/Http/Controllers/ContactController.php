<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\GlobalSection;
use App\Services\MailDispatchService;
use App\Support\PageDefaults;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show(MailDispatchService $mailDispatch)
    {
        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
        $purchasePopupProducts = function_exists('resolvePurchasePopupProducts')
            ? resolvePurchasePopupProducts($purchasePopups->content ?? [])
            : [];

        $support = $mailDispatch->resolveSupportContact(
            $footer->content ?? [],
            (string) (($footer->content['logo_url'] ?? '') ?: '/images/logo.svg')
        );

        return view('contact', [
            'globalHeader' => $header->content,
            'globalFooter' => $footer->content,
            'globalPurchasePopups' => $purchasePopups->content,
            'globalPurchasePopupProducts' => $purchasePopupProducts,
            'support' => $support,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
            'source_url' => ['nullable', 'string', 'max:2048'],
        ]);

        ContactMessage::create([
            'full_name' => trim((string) ($data['full_name'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'subject' => trim((string) ($data['subject'] ?? '')),
            'message' => trim((string) ($data['message'] ?? '')),
            'source_url' => trim((string) ($data['source_url'] ?? '')) ?: $request->fullUrl(),
            'ip_address' => (string) $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return redirect()
            ->route('contact.show')
            ->with('status', 'Thanks for contacting us. We will get back to you shortly.');
    }
}
