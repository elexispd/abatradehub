<?php

namespace App\Mail;

use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale,
        public SalePayment $payment
    ) {
        $this->sale->loadMissing(['items.product.media', 'payments']);
    }

    public function envelope(): Envelope
    {
        $company = config('abatradehub.name', 'AbaTradeHub');

        return new Envelope(
            subject: "[{$company}] Payment Receipt & Order Confirmation - {$this->sale->invoice_number}",
            replyTo: config('mail.from.address')
                ? [new Address(config('mail.from.address'), config('mail.from.name', $company))]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-receipt',
        );
    }
}

