<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorporateQuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    protected array $uploadedFiles;

    public function __construct(array $data, array $uploadedFiles = [])
    {
        $this->data = $data;
        $this->uploadedFiles = $uploadedFiles;
    }

    public function envelope(): Envelope
    {
        $subjectName = $this->data['company'] ?? $this->data['full_name'] ?? 'Corporate Quote';
        $replyTo = [];

        if (!empty($this->data['email']) && is_string($this->data['email'])) {
            $replyTo[] = new Address($this->data['email'], $this->data['full_name'] ?? '');
        }

        return new Envelope(
            subject: 'Corporate Quote Request - ' . $subjectName,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.corporate-quote',
            with: [
                'fields' => $this->buildFields(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->uploadedFiles as $file) {
            if (empty($file['path']) || !is_string($file['path'])) {
                continue;
            }

            $attachment = Attachment::fromPath($file['path']);

            if (!empty($file['name']) && is_string($file['name'])) {
                $attachment = $attachment->as($file['name']);
            }

            if (!empty($file['mime']) && is_string($file['mime'])) {
                $attachment = $attachment->withMime($file['mime']);
            }

            $attachments[] = $attachment;
        }

        return $attachments;
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
