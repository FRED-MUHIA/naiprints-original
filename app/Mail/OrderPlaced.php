<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public array $order;
    public array $support;

    public function __construct(array $order, array $support)
    {
        $this->order = $order;
        $this->support = $support;
    }

    public function build(): self
    {
        return $this->subject('Your Nai Prints order ' . ($this->order['number'] ?? ''))
            ->view('emails.order-placed');
    }
}
