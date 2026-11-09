<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCompletedThankYouMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale
    ) {
        $this->sale->loadMissing(['items.product.media', 'payments']);
    }

    public function envelope(): Envelope
    {
        $company = config('abatradehub.name', 'AbaTradeHub');

        return new Envelope(
            subject: "[{$company}] Thank You! Your Wholesale Order #{$this->sale->invoice_number} is Completed",
            replyTo: config('mail.from.address')
                ? [new Address(config('mail.from.address'), config('mail.from.name', $company))]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-completed-thank-you',
        );
    }
}

