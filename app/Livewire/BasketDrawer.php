<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\BasketService;
use App\Services\PaystackService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BasketDrawer extends Component
{
    public bool $isOpen = false;
    public string $step = 'basket'; // 'basket' or 'checkout'

    public array $items = [];
    public float $subtotal = 0.0;
    public string $subtotalFormatted = '₦0.00';
    public int $totalUnits = 0;

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
    public string $notes = '';

    /** Honeypot */
    public string $website = '';

    public ?string $errorMessage = null;
    public ?string $toastMessage = null;

    public function mount(BasketService $basket): void
    {
        $this->refreshBasketData($basket);
    }

    protected function refreshBasketData(BasketService $basket): void
    {
        $this->items = $basket->all();
        $this->subtotal = $basket->subtotal();
        $this->subtotalFormatted = $basket->subtotalFormatted();
        $this->totalUnits = $basket->totalUnits();
    }

    #[On('open-basket')]
    public function open(BasketService $basket): void
    {
        $this->refreshBasketData($basket);
        $this->step = 'basket';
        $this->errorMessage = null;
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->errorMessage = null;
    }

    #[On('add-to-basket')]
    public function addItem(BasketService $basket, ...$params): void
    {
        $payload = null;
        if (isset($params['product'])) {
            $payload = $params['product'];
        } elseif (isset($params[0]) && is_array($params[0])) {
            $payload = $params[0];
        } else {
            $payload = $params;
        }

        $productId = isset($payload['id']) ? (int) $payload['id'] : (int) ($payload['product_id'] ?? 0);
        $qty = isset($params['quantity']) ? (int) $params['quantity'] : (isset($payload['quantity']) ? (int) $payload['quantity'] : null);
        $specs = isset($params['specifications']) ? (string) $params['specifications'] : (isset($payload['specifications']) ? (string) $payload['specifications'] : null);

        if (! $productId) {
            return;
        }

        $product = Product::with('priceTiers')->find($productId);
        if (! $product) {
            return;
        }

        $basket->add($product, $qty, $specs);
        $this->refreshBasketData($basket);

        $this->toastMessage = "Added \"{$product->name}\" to your Wholesale Basket!";
        $this->dispatch('basket-updated');
        $this->isOpen = true;
    }

    public function increment(int $productId, BasketService $basket, int $step = 1): void
    {
        if (isset($this->items[$productId])) {
            $newQty = $this->items[$productId]['quantity'] + max(1, $step);
            $basket->updateQuantity($productId, $newQty);
            $this->refreshBasketData($basket);
            $this->dispatch('basket-updated');
        }
    }

    public function decrement(int $productId, BasketService $basket, int $step = 1): void
    {
        if (isset($this->items[$productId])) {
            $minMoq = max(1, (int) ($this->items[$productId]['moq'] ?? 1));
            $newQty = max($minMoq, $this->items[$productId]['quantity'] - max(1, $step));
            $basket->updateQuantity($productId, $newQty);
            $this->refreshBasketData($basket);
            $this->dispatch('basket-updated');
        }
    }

    public function updateItemQuantity(int $productId, $quantity, BasketService $basket): void
    {
        if (isset($this->items[$productId])) {
            $minMoq = max(1, (int) ($this->items[$productId]['moq'] ?? 1));
            $newQty = max($minMoq, (int) $quantity);
            $basket->updateQuantity($productId, $newQty);
            $this->refreshBasketData($basket);
            $this->dispatch('basket-updated');
        }
    }

    public function removeItem(int $productId, BasketService $basket): void
    {
        $basket->remove($productId);
        $this->refreshBasketData($basket);
        $this->dispatch('basket-updated');

        if (empty($this->items)) {
            $this->step = 'basket';
        }
    }

    public function proceedToCheckout(): void
    {
        $this->errorMessage = null;

        if (empty($this->items)) {
            $this->errorMessage = 'Your basket is empty. Please add wholesale items before proceeding.';
            return;
        }

        $this->step = 'checkout';
    }

    public function backToBasket(): void
    {
        $this->errorMessage = null;
        $this->step = 'basket';
    }

    public function checkout(BasketService $basket, PaystackService $paystack)
    {
        $this->errorMessage = null;

        $key = 'basket-checkout:' . request()->ip();
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
            'notes' => 'nullable|string|max:1000',
        ], [
            'destination_city.required' => 'Please provide your delivery city or commercial transport park.',
        ]);

        RateLimiter::hit($key, 60);

        if ($this->website !== '') {
            $this->close();
            return;
        }

        $basketItems = $basket->all();
        if (empty($basketItems)) {
            $this->errorMessage = 'Your wholesale basket is empty.';
            $this->step = 'basket';
            return;
        }

        // Verify products and calculate exact verified subtotal
        $productIds = array_keys($basketItems);
        $products = Product::with('priceTiers')->whereIn('id', $productIds)->get()->keyBy('id');

        $verifiedItems = [];
        $totalSubtotal = 0.0;
        $totalUnits = 0;

        foreach ($basketItems as $pid => $item) {
            $product = $products->get($pid);
            if (! $product) {
                continue;
            }

            $moq = max(1, (int) $product->moq);
            $qty = max($moq, (int) ($item['quantity'] ?? $moq));
            $unitPrice = $product->getUnitPriceForQuantity($qty);
            $itemTotal = round($qty * $unitPrice, 2);

            $verifiedItems[] = [
                'product' => $product,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
                'specifications' => $item['specifications'] ?? null,
            ];

            $totalSubtotal += $itemTotal;
            $totalUnits += $qty;
        }

        if (empty($verifiedItems)) {
            $this->errorMessage = 'None of the items in your basket are currently available.';
            $this->step = 'basket';
            return;
        }

        $totalSubtotal = round($totalSubtotal, 2);

        // Create central Sale record
        $sale = Sale::create([
            'invoice_number' => Sale::generateInvoiceNumber(),
            'created_by' => null,
            'updated_by' => null,
            'customer_name' => $this->name,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone,
            'destination_country' => $this->country,
            'destination_city' => $this->destination_city,
            'shipping_address' => $this->shipping_address ?: null,
            'customer_notes' => $this->notes ?: null,
            'internal_notes' => 'Placed via online Wholesale Basket checkout (Paystack)',
            'status' => SaleStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'currency' => 'NGN',
            'subtotal' => $totalSubtotal,
            'shipping_fee' => 0.00,
            'inspection_fee' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => $totalSubtotal,
            'amount_paid' => 0.00,
            'balance_due' => $totalSubtotal,
            'order_date' => now()->toDateString(),
        ]);

        foreach ($verifiedItems as $vItem) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $vItem['product']->id,
                'item_name' => $vItem['product']->name,
                'item_sku' => $vItem['product']->sku,
                'quantity' => $vItem['quantity'],
                'unit_price' => $vItem['unit_price'],
                'total_price' => $vItem['total_price'],
                'specifications' => $vItem['specifications'],
            ]);
        }

        // Initialize central Paystack transaction
        $reference = 'ATH-PAY-' . $sale->id . '-' . time() . '-' . Str::lower(Str::random(5));
        $amountInKobo = (int) round($totalSubtotal * 100);

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
                'items_count' => count($verifiedItems),
                'total_units' => $totalUnits,
            ],
        ]);

        if (! empty($initResponse['status']) && ! empty($initResponse['data']['authorization_url'])) {
            $basket->clear();
            $this->dispatch('basket-updated');
            return redirect()->away($initResponse['data']['authorization_url']);
        }

        $this->errorMessage = $initResponse['message'] ?? 'Failed to initialize central Paystack payment. Please try again.';
    }

    public function render()
    {
        $countries = config('abatradehub.countries', ['Nigeria', 'Ghana', 'Benin Republic']);

        return view('livewire.basket-drawer', [
            'countries' => $countries,
        ]);
    }
}

