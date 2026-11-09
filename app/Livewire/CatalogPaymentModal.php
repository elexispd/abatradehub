<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\PaystackService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CatalogPaymentModal extends Component
{
    public bool $isOpen = false;

    public ?int $productId = null;
    public string $productName = '';
    public string $productSku = '';
    public ?string $productImage = null;
    public string $productUrl = '';
    public int $productMoq = 10;
    public string $productMoqUnit = 'pcs';
    public float $basePrice = 0.0;
    public float $unitPrice = 0.0;
    public int $quantity = 10;
    public string $currency = 'NGN';
    public array $priceTiers = [];

    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    #[Validate('required|string|min:7|max:25|regex:/^[0-9+\-\s()]+$/')]
    public string $phone = '';

    #[Validate('required|string|max:100')]
    public string $country = 'Nigeria';

    #[Validate('required|string|max:150')]
    public string $destination_city = '';

    #[Validate('nullable|string|max:255')]
    public string $shipping_address = '';

    #[Validate('nullable|string|max:1000')]
    public string $specifications = '';

    /** Honeypot */
    public string $website = '';

    public ?string $errorMessage = null;

    #[On('open-payment-modal')]
    public function open(...$params): void
    {
        $this->resetValidation();
        $this->errorMessage = null;

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
        $this->productImage = (string) ($product['primary_image'] ?? '');
        $this->productUrl = (string) ($product['url'] ?? '');
        $this->productMoq = max(1, (int) ($product['moq'] ?? 10));
        $this->productMoqUnit = (string) ($product['moq_unit'] ?? 'pcs');
        $this->basePrice = (float) ($product['base_price'] ?? ($product['starting_price'] ?? 0));
        $this->currency = (string) ($product['currency'] ?? 'NGN');
        $this->priceTiers = (array) ($product['price_tiers'] ?? []);

        $this->quantity = $this->productMoq;
        $this->recalculatePricing();

        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->errorMessage = null;
    }

    public function updatedQuantity(): void
    {
        $this->recalculatePricing();
    }

    public function incrementQuantity(int $step = 5): void
    {
        $this->quantity += max(1, $step);
        $this->recalculatePricing();
    }

    public function decrementQuantity(int $step = 5): void
    {
        $this->quantity = max($this->productMoq, $this->quantity - max(1, $step));
        $this->recalculatePricing();
    }

    protected function recalculatePricing(): void
    {
        $effectivePrice = $this->basePrice;

        if (! empty($this->priceTiers)) {
            foreach ($this->priceTiers as $tier) {
                $min = (int) ($tier['min_quantity'] ?? 0);
                $max = isset($tier['max_quantity']) && $tier['max_quantity'] !== null ? (int) $tier['max_quantity'] : null;
                $tierUnitPrice = (float) ($tier['unit_price'] ?? 0);

                if ($this->quantity >= $min && ($max === null || $this->quantity <= $max)) {
                    $effectivePrice = $tierUnitPrice;
                    break;
                }
            }
        }

        $this->unitPrice = $effectivePrice;
    }

    public function getSubtotalProperty(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }

    public function getSubtotalFormattedProperty(): string
    {
        return \App\Support\Money::format($this->subtotal, $this->currency);
    }

    public function getUnitPriceFormattedProperty(): string
    {
        return \App\Support\Money::format($this->unitPrice, $this->currency);
    }

    public function pay(PaystackService $paystack)
    {
        $this->errorMessage = null;

        $key = 'catalog-payment:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            $this->addError('email', 'Too many checkout attempts. Please wait a moment before trying again.');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|min:7|max:25|regex:/^[0-9+\-\s()]+$/',
            'country' => 'required|string|max:100',
            'destination_city' => 'required|string|max:150',
            'shipping_address' => 'nullable|string|max:255',
            'specifications' => 'nullable|string|max:1000',
            'quantity' => "required|integer|min:{$this->productMoq}",
        ], [
            'quantity.min' => "The minimum order quantity for this item is {$this->productMoq} {$this->productMoqUnit}.",
            'destination_city.required' => 'Please provide your delivery city or transport park.',
        ]);

        RateLimiter::hit($key, 60);

        // Honeypot check
        if ($this->website !== '') {
            $this->close();
            return;
        }

        // Fresh database fetch to prevent tampering
        $product = Product::with('priceTiers')->find($this->productId);
        if (! $product) {
            $this->errorMessage = 'Selected product is no longer available.';
            return;
        }

        $verifiedUnitPrice = $product->getUnitPriceForQuantity($this->quantity);
        $verifiedSubtotal = round($this->quantity * $verifiedUnitPrice, 2);

        // Generate sale record
        $sale = Sale::create([
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_name' => $this->name,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone,
            'destination_country' => $this->country,
            'destination_city' => $this->destination_city,
            'shipping_address' => $this->shipping_address ?: null,
            'customer_notes' => $this->specifications ?: null,
            'status' => SaleStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'currency' => 'NGN',
            'subtotal' => $verifiedSubtotal,
            'shipping_fee' => 0.00,
            'inspection_fee' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => $verifiedSubtotal,
            'amount_paid' => 0.00,
            'balance_due' => $verifiedSubtotal,
            'order_date' => now()->toDateString(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'item_name' => $product->name,
            'item_sku' => $product->sku,
            'quantity' => $this->quantity,
            'unit_price' => $verifiedUnitPrice,
            'total_price' => $verifiedSubtotal,
            'specifications' => $this->specifications ?: null,
        ]);

        // Paystack reference & Kobo conversion (1 NGN = 100 Kobo)
        $reference = 'ATH-PAY-' . $sale->id . '-' . time() . '-' . Str::lower(Str::random(5));
        $amountInKobo = (int) round($verifiedSubtotal * 100);

        $initResponse = $paystack->initializeTransaction([
            'amount' => $amountInKobo,
            'email' => $this->email,
            'reference' => $reference,
            'callback_url' => route('payment.callback'),
            'metadata' => [
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'customer_name' => $this->name,
                'customer_phone' => $this->phone,
                'product_id' => $product->id,
                'quantity' => $this->quantity,
            ],
        ]);

        if (! empty($initResponse['status']) && ! empty($initResponse['data']['authorization_url'])) {
            return redirect()->away($initResponse['data']['authorization_url']);
        }

        $this->errorMessage = $initResponse['message'] ?? 'Failed to initialize Paystack checkout. Please try again or contact us.';
    }

    public function render()
    {
        $countries = config('abatradehub.countries', ['Nigeria', 'Ghana', 'Benin Republic']);

        return view('livewire.catalog-payment-modal', [
            'countries' => $countries,
        ]);
    }
}
