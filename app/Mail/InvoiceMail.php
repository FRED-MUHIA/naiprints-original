<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use SerializesModels;

    public Invoice $invoice;

    /**
     * @var array<int, array{path: string, name?: string, mime?: string}>
     */
    public array $extraAttachments;

    /**
     * Create a new message instance.
     *
     * @param array<int, array{path: string, name?: string, mime?: string}> $extraAttachments
     */
    public function __construct(Invoice $invoice, array $extraAttachments = [])
    {
        // Keep this mailable free of Queueable/$afterCommit property conflicts.
        $this->invoice = $invoice->loadMissing(['client', 'items']);
        $this->extraAttachments = $extraAttachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->number} from Nai Prints",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return collect($this->extraAttachments)
            ->filter(fn (array $attachment) => is_file($attachment['path'] ?? ''))
            ->map(function (array $attachment) {
                $mailAttachment = Attachment::fromPath($attachment['path']);

                if (!empty($attachment['name'])) {
                    $mailAttachment = $mailAttachment->as($attachment['name']);
                }

                if (!empty($attachment['mime'])) {
                    $mailAttachment = $mailAttachment->withMime($attachment['mime']);
                }

                return $mailAttachment;
            })
            ->values()
            ->all();
    }
}
