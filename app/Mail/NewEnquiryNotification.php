<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEnquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Enquiry $enquiry
    ) {
        $this->enquiry->loadMissing('items.product');
    }

    public function envelope(): Envelope
    {
        $company = config('abatradehub.name', 'AbaTradeHub');
        $firstItem = $this->enquiry->items->first();
        $itemSummary = $firstItem
            ? " - {$firstItem->quantity}x {$firstItem->product_name}"
            : '';

        return new Envelope(
            subject: "[{$company}] New Wholesale Enquiry: {$this->enquiry->reference}{$itemSummary}",
            replyTo: $this->enquiry->email ? [new Address($this->enquiry->email, $this->enquiry->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-enquiry-notification',
        );
    }
}

