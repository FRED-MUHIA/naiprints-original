<?php

namespace App\Mail;

use App\Models\InvoiceSetting;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use SerializesModels;

    public Quotation $quotation;

    /**
     * Create a new message instance.
     */
    public function __construct(Quotation $quotation)
    {
        $this->quotation = $quotation->loadMissing(['client', 'items']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quotation {$this->quotation->number} from Nai Prints",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->buildQuotationPdf(),
                "{$this->quotation->number}.pdf"
            )->withMime('application/pdf'),
        ];
    }

    private function buildQuotationPdf(): string
    {
        $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());

        return Pdf::loadView('quotations.pdf', [
            'quotation' => $this->quotation,
            'settings' => $settings,
        ])
            ->setOption('isRemoteEnabled', true)
            ->output();
    }
}
