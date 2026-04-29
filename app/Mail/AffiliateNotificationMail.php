<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AffiliateNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $messageBody,
        public ?Affiliate $affiliate = null
    ) {
    }

    public function build(): self
    {
        $mail = $this->subject($this->subjectLine)
            ->view('emails.affiliate.notification');

        $logoPath = public_path('images/logo.svg');
        if (is_file($logoPath)) {
            $mail->attach($logoPath, [
                'as' => 'nai-prints-logo.svg',
                'mime' => 'image/svg+xml',
            ]);
        }

        return $mail;
    }
}
