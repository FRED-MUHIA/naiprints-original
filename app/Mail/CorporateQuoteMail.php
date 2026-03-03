<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorporateQuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public array $attachments;

    public function __construct(array $data, array $attachments = [])
    {
        $this->data = $data;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $subjectName = $this->data['company'] ?? $this->data['full_name'] ?? 'Corporate Quote';

        $mail = $this->subject('Corporate Quote Request - ' . $subjectName)
            ->view('emails.corporate-quote')
            ->with([
                'fields' => $this->buildFields(),
            ]);

        if (!empty($this->data['email'])) {
            $mail->replyTo($this->data['email'], $this->data['full_name'] ?? null);
        }

        foreach ($this->attachments as $attachment) {
            if (!empty($attachment['path'])) {
                $mail->attach($attachment['path'], [
                    'as' => $attachment['name'] ?? 'artwork',
                    'mime' => $attachment['mime'] ?? null,
                ]);
            }
        }

        return $mail;
    }

    private function buildFields(): array
    {
        $sizes = [
            'XS' => $this->data['size_xs'] ?? null,
            'S' => $this->data['size_s'] ?? null,
            'M' => $this->data['size_m'] ?? null,
            'L' => $this->data['size_l'] ?? null,
            'XL' => $this->data['size_xl'] ?? null,
            'XXL' => $this->data['size_xxl'] ?? null,
            'XXXL' => $this->data['size_xxxl'] ?? null,
        ];

        $sizeSummary = collect($sizes)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $label) => $label . ': ' . $value)
            ->implode(', ');

        return [
            'Name' => $this->data['full_name'] ?? '',
            'Company' => $this->data['company'] ?? '',
            'Role / Department' => $this->data['role'] ?? '',
            'Phone' => $this->data['phone'] ?? '',
            'Email' => $this->data['email'] ?? '',
            'Delivery/Shipping Address' => $this->data['location'] ?? '',
            'Product Type' => $this->data['product_type'] ?? '',
            'Expected Delivery Date' => $this->data['delivery_date'] ?? '',
            'Total Quantity' => $this->data['total_quantity'] ?? '',
            'Size Breakdown' => $sizeSummary,
            'Primary Color' => $this->data['color'] ?? '',
            'Branding Method' => $this->data['branding'] ?? '',
            'Budget Range' => $this->data['budget'] ?? '',
            'Additional Comments' => $this->data['comments'] ?? '',
        ];
    }
}
