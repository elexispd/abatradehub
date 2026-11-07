<?php

namespace App\Livewire;

use App\Mail\NewEnquiryNotification;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CatalogEnquiryModal extends Component
{
    public bool $isOpen = false;

    public ?int $productId = null;
    public string $productName = '';
    public string $productSku = '';
    public string $productPrice = '';
    public int $productMoq = 10;
    public string $productUrl = '';

    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|string|min:7|max:25|regex:/^[0-9+\-\s()]+$/')]
    public string $phone = '';

    #[Validate('required|string|max:100')]
    public string $country = 'Nigeria';

    #[Validate('required|integer|min:1')]
    public int $quantity = 10;

    #[Validate('nullable|string|max:150')]
    public string $destination = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    /** Honeypot */
    public string $website = '';

    public bool $submitted = false;
    public string $whatsappRedirectUrl = '';

    #[On('open-enquiry-modal')]
    public function open(...$params): void
    {
        $this->resetValidation();
        $this->submitted = false;

        if (isset($params['product']) && is_array($params['product'])) {
            $product = $params['product'];
        } elseif (isset($params[0]) && is_array($params[0])) {
            $product = $params[0];
        } else {
            $product = $params;
        }

        $this->productId = isset($product['id']) ? (int) $product['id'] : null;
        $this->productName = (string) ($product['name'] ?? '');
        $this->productSku = (string) ($product['sku'] ?? '');
        $this->productPrice = (string) ($product['starting_price_formatted'] ?? ($product['base_price_formatted'] ?? '₦0'));
        $this->productMoq = max(1, (int) ($product['moq'] ?? 10));
        $this->quantity = $this->productMoq;
        $this->productUrl = (string) ($product['url'] ?? '');

        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->submitted = false;
    }

    public function submit(): void
    {
        $key = 'catalog-enquiry:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 6)) {
            $this->addError('phone', 'Too many requests. Please wait a minute or message us on WhatsApp.');

            return;
        }

        $this->validate([
            'name' => 'required|string|max:120',
            'phone' => 'required|string|min:7|max:25|regex:/^[0-9+\-\s()]+$/',
            'country' => 'required|string|max:100',
            'quantity' => "required|integer|min:{$this->productMoq}",
            'destination' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ], [
            'quantity.min' => "The minimum order quantity for this item is {$this->productMoq} pieces.",
        ]);

        RateLimiter::hit($key, 60);

        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $enquiry = Enquiry::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'country' => $this->country,
            'city' => $this->destination ?: null,
            'message' => $this->notes ?: "Enquiry for {$this->quantity} pcs of {$this->productName}",
            'source' => 'catalog',
        ]);

        EnquiryItem::create([
            'enquiry_id' => $enquiry->id,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'quantity' => $this->quantity,
            'note' => $this->notes ?: null,
        ]);

        $adminEmail = config('abatradehub.enquiry_notification_email');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new NewEnquiryNotification($enquiry));
            } catch (\Throwable $e) {
                Log::error('Failed sending admin enquiry email: ' . $e->getMessage());
            }
        }

        // Build prefilled WhatsApp message
        $waMessage = "Hello ".config('abatradehub.name').", I have submitted an enquiry ({$enquiry->reference}) for:\n"
            ."Product: {$this->productName} (SKU: {$this->productSku})\n"
            ."Quantity: {$this->quantity} pcs\n"
            ."Country: {$this->country}\n"
            .($this->destination ? "Destination: {$this->destination}\n" : '')
            .($this->notes ? "Notes: {$this->notes}\n" : '')
            ."Link: {$this->productUrl}";

        $this->whatsappRedirectUrl = \App\Support\Whatsapp::url($waMessage);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.catalog-enquiry-modal');
    }
}

