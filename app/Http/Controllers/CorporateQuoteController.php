<?php

namespace App\Http\Controllers;

use App\Mail\CorporateQuoteMail;
use App\Services\MailDispatchService;
use Illuminate\Http\Request;

class CorporateQuoteController extends Controller
{
    public function store(Request $request, MailDispatchService $mailDispatch)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:160'],
            'role' => ['nullable', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:160'],
            'location' => ['nullable', 'string', 'max:200'],
            'product_type' => ['required', 'string', 'max:200'],
            'delivery_date' => ['nullable', 'date'],
            'total_quantity' => ['required', 'integer', 'min:1'],
            'size_xs' => ['nullable', 'integer', 'min:0'],
            'size_s' => ['nullable', 'integer', 'min:0'],
            'size_m' => ['nullable', 'integer', 'min:0'],
            'size_l' => ['nullable', 'integer', 'min:0'],
            'size_xl' => ['nullable', 'integer', 'min:0'],
            'size_xxl' => ['nullable', 'integer', 'min:0'],
            'size_xxxl' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:120'],
            'branding' => ['nullable', 'string', 'max:120'],
            'artwork' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,svg,pdf'],
            'budget' => ['nullable', 'string', 'max:120'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        $attachments = [];
        if ($request->hasFile('artwork')) {
            $file = $request->file('artwork');
            $attachments[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ];
        }

        $delivery = $mailDispatch->sendToRecipients(
            [
                (string) config('mail.support.address', config('mail.from.address')),
                (string) ($data['email'] ?? ''),
            ],
            fn () => new CorporateQuoteMail($data, $attachments),
            [
                'event' => 'corporate.quote_mail',
                'submitted_email' => (string) ($data['email'] ?? ''),
            ]
        );

        if (empty($delivery['sent'])) {
            return back()
                ->withErrors(['email' => 'Quote request was received, but email delivery failed. Please try again.'])
                ->withInput();
        }

        $status = empty($delivery['failed'])
            ? 'Thanks! Your quote request has been submitted.'
            : 'Quote submitted, but one or more email notifications failed to send.';

        return back()->with('status', $status);
    }
}
