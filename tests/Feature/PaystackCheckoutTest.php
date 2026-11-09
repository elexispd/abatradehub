<?php

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Livewire\BasketCounter;
use App\Livewire\BasketDrawer;
use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Services\BasketService;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('displays enquiry button with signature primary-container color and basket button', function () {
    $category = Category::factory()->create(['name' => 'Footwear Hub', 'slug' => 'footwear-hub']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Handmade Leather Oxford Shoes',
        'slug' => 'handmade-leather-oxford-shoes',
        'moq' => 10,
        'base_price' => 25000,
    ]);

    // Catalog page contains enquiry button with bg-primary-container (#c05621) and + Basket button
    $this->get('/products')
        ->assertOk()
        ->assertSee('bg-primary-container', false)
        ->assertSee('Enquire')
        ->assertSee('+ Basket');

    // Product detail page contains Submit Bulk Enquiry (bg-primary-container) and Add to Wholesale Basket
    $this->get('/products/footwear-hub/handmade-leather-oxford-shoes')
        ->assertOk()
        ->assertSee('bg-primary-container', false)
        ->assertSee('Submit Bulk Enquiry')
        ->assertSee('Add to Wholesale Basket');
});

it('supports adding multiple wholesale products to basket with MOQ enforcement and tier pricing', function () {
    $category = Category::factory()->create();
    $prodA = Product::factory()->for($category)->create([
        'name' => 'Senator Suit 2-Piece',
        'sku' => 'ATH-SN-200',
        'moq' => 10,
        'base_price' => 22000,
    ]);

    PriceTier::create([
        'product_id' => $prodA->id,
        'min_quantity' => 10,
        'max_quantity' => 49,
        'unit_price' => 22000,
    ]);
    PriceTier::create([
        'product_id' => $prodA->id,
        'min_quantity' => 50,
        'max_quantity' => null,
        'unit_price' => 19000,
    ]);

    $prodB = Product::factory()->for($category)->create([
        'name' => 'Leather Suede Loafers',
        'sku' => 'ATH-SH-300',
        'moq' => 15,
        'base_price' => 16000,
    ]);

    /** @var BasketService $basket */
    $basket = app(BasketService::class);
    $basket->clear();

    // Add Product A with MOQ 10
    $basket->add($prodA, 10);
    expect($basket->count())->toBe(1)
        ->and($basket->totalUnits())->toBe(10)
        ->and($basket->subtotal())->toEqual(220000.00); // 10 * 22,000

    // Add Product B with MOQ 15
    $basket->add($prodB, 15);
    expect($basket->count())->toBe(2)
        ->and($basket->totalUnits())->toBe(25)
        ->and($basket->subtotal())->toEqual(460000.00); // 220,000 + (15 * 16,000 = 240,000)

    // Update Product A quantity to 50 (should trigger lower wholesale tier price of 19,000)
    $basket->updateQuantity($prodA->id, 50);
    expect($basket->get($prodA->id)['unit_price'])->toEqual(19000.00)
        ->and($basket->get($prodA->id)['total_price'])->toEqual(950000.00) // 50 * 19,000
        ->and($basket->subtotal())->toEqual(1190000.00); // 950,000 + 240,000
});

it('renders basket drawer and counter components with live updates', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'name' => 'Ankara Maxi Dress',
        'sku' => 'ATH-DR-10',
        'moq' => 10,
        'base_price' => 12000,
    ]);

    /** @var BasketService $basket */
    $basket = app(BasketService::class);
    $basket->clear();

    Livewire::test(BasketCounter::class)
        ->assertSet('count', 0)
        ->assertDontSee('bg-secondary-container');

    Livewire::test(BasketDrawer::class)
        ->dispatch('add-to-basket', product: $product->toQuickViewArray())
        ->assertSet('isOpen', true)
        ->assertSee('Ankara Maxi Dress')
        ->assertSee('ATH-DR-10')
        ->assertSee('Wholesale Basket')
        ->call('increment', $product->id, 1)
        ->assertSee('11')
        ->call('decrement', $product->id, 1)
        ->assertSee('10')
        ->call('updateItemQuantity', $product->id, 14)
        ->assertSee('14');

    expect($basket->count())->toBe(1);
});

it('performs central wholesale checkout for basket via Paystack', function () {
    Http::fake([
        'https://api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/central-pay-code-99',
                'access_code' => 'central-pay-code-99',
                'reference' => 'ATH-PAY-CENTRAL-MOCK',
            ],
        ], 200),
    ]);

    $category = Category::factory()->create();
    $prod1 = Product::factory()->for($category)->create([
        'name' => 'Italian Wool Senator Set',
        'sku' => 'ATH-SN-11',
        'moq' => 10,
        'base_price' => 20000,
    ]);
    $prod2 = Product::factory()->for($category)->create([
        'name' => 'Handcrafted Leather Slides',
        'sku' => 'ATH-SL-22',
        'moq' => 10,
        'base_price' => 10000,
    ]);

    /** @var BasketService $basket */
    $basket = app(BasketService::class);
    $basket->clear();
    $basket->add($prod1, 10); // 200,000
    $basket->add($prod2, 10); // 100,000
    // Total basket = 300,000

    Livewire::test(BasketDrawer::class)
        ->dispatch('open-basket')
        ->assertSet('step', 'basket')
        ->call('proceedToCheckout')
        ->assertSet('step', 'checkout')
        ->set('name', 'Nkem Chukwu')
        ->set('email', 'nkem@example.com')
        ->set('phone', '+234 803 777 6666')
        ->set('country', 'Nigeria')
        ->set('destination_city', 'Onitsha (Main Market Park)')
        ->set('shipping_address', 'Zone 3, Line 5')
        ->set('notes', 'Senator: 5 Black, 5 Navy. Slides: Size 42 to 45.')
        ->call('checkout')
        ->assertRedirect('https://checkout.paystack.com/central-pay-code-99');

    // Basket should be cleared
    expect($basket->count())->toBe(0);

    // Verify Sale record created with multiple SaleItems
    $sale = Sale::where('customer_email', 'nkem@example.com')->first();
    expect($sale)->not->toBeNull()
        ->and($sale->total_amount)->toEqual(300000.00)
        ->and($sale->status)->toBe(SaleStatus::PendingPayment)
        ->and($sale->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($sale->items)->toHaveCount(2);

    $skus = $sale->items->pluck('item_sku')->all();
    expect($skus)->toContain('ATH-SN-11')
        ->and($skus)->toContain('ATH-SL-22');
});

it('verifies paystack transaction on callback and updates order to paid', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-9901',
        'customer_name' => 'Chioma Okeke',
        'customer_email' => 'chioma@example.com',
        'customer_phone' => '+234 809 999 8888',
        'destination_country' => 'Nigeria',
        'destination_city' => 'Abuja (Utako)',
        'status' => SaleStatus::PendingPayment,
        'payment_status' => PaymentStatus::Unpaid,
        'currency' => 'NGN',
        'subtotal' => 150000.00,
        'total_amount' => 150000.00,
        'balance_due' => 150000.00,
        'amount_paid' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Sample wholesale item',
        'quantity' => 10,
        'unit_price' => 15000.00,
        'total_price' => 150000.00,
    ]);

    $mockReference = "ATH-PAY-{$sale->id}-1731057600-abcde";

    Http::fake([
        "https://api.paystack.co/transaction/verify/{$mockReference}" => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'status' => 'success',
                'reference' => $mockReference,
                'amount' => 15000000, // 150,000 NGN in kobo
                'channel' => 'card',
                'metadata' => [
                    'sale_id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                ],
            ],
        ], 200),
    ]);

    $response = $this->get(route('payment.callback', ['reference' => $mockReference]));

    $response->assertRedirect(route('payment.success', [
        'sale' => $sale->id,
        'reference' => $mockReference,
    ]));

    $sale->refresh();
    expect($sale->status)->toBe(SaleStatus::Processing)
        ->and($sale->payment_status)->toBe(PaymentStatus::Paid)
        ->and($sale->amount_paid)->toEqual(150000.00)
        ->and($sale->balance_due)->toEqual(0.00)
        ->and($sale->payments)->toHaveCount(1)
        ->and($sale->payments->first()->reference)->toBe($mockReference);
});

it('processes paystack webhook with signature verification', function () {
    config(['services.paystack.secret_key' => 'sk_test_mock_secret_key_123']);

    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-9902',
        'customer_name' => 'Babatunde Fash',
        'customer_email' => 'tunde@example.com',
        'customer_phone' => '+234 802 333 4444',
        'destination_country' => 'Nigeria',
        'destination_city' => 'Ibadan (Iwo Road)',
        'status' => SaleStatus::PendingPayment,
        'payment_status' => PaymentStatus::Unpaid,
        'currency' => 'NGN',
        'subtotal' => 200000.00,
        'total_amount' => 200000.00,
        'balance_due' => 200000.00,
        'amount_paid' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Sample wholesale item 2',
        'quantity' => 20,
        'unit_price' => 10000.00,
        'total_price' => 200000.00,
    ]);

    $reference = "ATH-PAY-{$sale->id}-webhook-test";
    $payload = json_encode([
        'event' => 'charge.success',
        'data' => [
            'status' => 'success',
            'reference' => $reference,
            'amount' => 20000000,
            'channel' => 'bank_transfer',
            'metadata' => [
                'sale_id' => $sale->id,
            ],
        ],
    ]);

    $validSignature = hash_hmac('sha512', $payload, 'sk_test_mock_secret_key_123');

    // Test with invalid signature
    $this->postJson(route('payment.webhook'), json_decode($payload, true), [
        'x-paystack-signature' => 'invalid-signature',
    ])->assertStatus(400);

    // Test with valid signature
    $response = $this->call(
        'POST',
        route('payment.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $validSignature,
        ],
        $payload
    );

    $response->assertOk()
        ->assertJson(['status' => true]);

    $sale->refresh();
    expect($sale->status)->toBe(SaleStatus::Processing)
        ->and($sale->payment_status)->toBe(PaymentStatus::Paid)
        ->and($sale->amount_paid)->toEqual(200000.00)
        ->and($sale->payments)->toHaveCount(1)
        ->and($sale->payments->first()->payment_method)->toBe('paystack');
});

it('attributes online wholesale orders and payments to the online customer in audit logs', function () {
    // Unauthenticated customer checkout
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-9903',
        'customer_name' => 'Ngozi Eze',
        'customer_email' => 'ngozi@example.com',
        'customer_phone' => '+234 803 111 2222',
        'status' => SaleStatus::PendingPayment,
        'payment_status' => PaymentStatus::Unpaid,
        'currency' => 'NGN',
        'subtotal' => 80000.00,
        'total_amount' => 80000.00,
        'balance_due' => 80000.00,
        'amount_paid' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Wholesale Kaftan',
        'quantity' => 10,
        'unit_price' => 8000.00,
        'total_price' => 80000.00,
    ]);

    // Check creation audit log
    $creationLog = $sale->auditLogs()->where('action', 'created')->first();
    expect($creationLog)->not->toBeNull()
        ->and($creationLog->user_id)->toBeNull()
        ->and($creationLog->user_name)->toBe('Online Customer (Ngozi Eze)')
        ->and($creationLog->description)->toContain('Online wholesale order')
        ->and($creationLog->description)->toContain('Ngozi Eze');

    // Simulate Paystack payment recording
    SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 80000.00,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'paystack',
        'reference' => "ATH-PAY-{$sale->id}-test-ref",
        'notes' => 'Paystack card payment',
    ]);

    $paymentLog = $sale->auditLogs()->where('action', 'payment_recorded')->first();
    expect($paymentLog)->not->toBeNull()
        ->and($paymentLog->user_id)->toBeNull()
        ->and($paymentLog->user_name)->toBe('Online Customer (Ngozi Eze)')
        ->and($paymentLog->description)->toContain('Online payment')
        ->and($paymentLog->description)->toContain('Ngozi Eze');
});

it('handles cancelled or abandoned paystack payments on callback with audit trail', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-9904',
        'customer_name' => 'Emeka Okafor',
        'customer_email' => 'emeka@example.com',
        'customer_phone' => '+234 805 444 5555',
        'status' => SaleStatus::PendingPayment,
        'payment_status' => PaymentStatus::Unpaid,
        'currency' => 'NGN',
        'subtotal' => 60000.00,
        'total_amount' => 60000.00,
        'balance_due' => 60000.00,
        'amount_paid' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Leather Slippers',
        'quantity' => 10,
        'unit_price' => 6000.00,
        'total_price' => 60000.00,
    ]);

    $ref = "ATH-PAY-{$sale->id}-cancelled-test";

    // Simulate Paystack returning abandoned/cancelled status
    Http::fake([
        "https://api.paystack.co/transaction/verify/{$ref}" => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'status' => 'abandoned',
                'gateway_response' => 'Transaction was cancelled by customer',
                'reference' => $ref,
                'metadata' => ['sale_id' => $sale->id],
            ],
        ], 200),
    ]);

    $response = $this->get(route('payment.callback', ['reference' => $ref]));

    $response->assertRedirect(route('products.index'))
        ->assertSessionHas('error');

    $sale->refresh();
    expect($sale->status)->toBe(SaleStatus::Cancelled)
        ->and($sale->internal_notes)->toContain('cancelled or abandoned');

    $cancelLog = $sale->auditLogs()->where('action', 'payment_cancelled')->first();
    expect($cancelLog)->not->toBeNull()
        ->and($cancelLog->user_name)->toBe('Online Customer (Emeka Okafor)')
        ->and($cancelLog->description)->toContain('Emeka Okafor')
        ->and($cancelLog->description)->toContain('cancelled');
});

it('sends receipt email with product images to customer when online payment completes', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'name' => 'Italian Leather Loafers',
        'sku' => 'ATH-LF-99',
        'base_price' => 30000,
    ]);

    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-9905',
        'customer_name' => 'Amara Nnadi',
        'customer_email' => 'amara.nnadi@example.com',
        'customer_phone' => '+234 809 111 4455',
        'destination_country' => 'Nigeria',
        'destination_city' => 'Port Harcourt (Mile 1)',
        'shipping_address' => 'Shop 12 Line 4',
        'status' => SaleStatus::PendingPayment,
        'payment_status' => PaymentStatus::Unpaid,
        'currency' => 'NGN',
        'subtotal' => 60000.00,
        'total_amount' => 60000.00,
        'balance_due' => 60000.00,
        'amount_paid' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'item_name' => $product->name,
        'item_sku' => $product->sku,
        'quantity' => 2,
        'unit_price' => 30000.00,
        'total_price' => 60000.00,
        'specifications' => 'Size 43 & 44',
    ]);

    $ref = "ATH-PAY-{$sale->id}-receipt-email-test";

    Http::fake([
        "https://api.paystack.co/transaction/verify/{$ref}" => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'status' => 'success',
                'reference' => $ref,
                'amount' => 6000000, // 60,000 NGN in kobo
                'channel' => 'card',
                'metadata' => [
                    'sale_id' => $sale->id,
                ],
            ],
        ], 200),
    ]);

    $response = $this->get(route('payment.callback', ['reference' => $ref]));
    $response->assertRedirect(route('payment.success', [
        'sale' => $sale->id,
        'reference' => $ref,
    ]));

    // Assert that the receipt email was sent to customer
    \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OrderReceiptMail::class, function ($mail) use ($sale, $ref) {
        $hasTo = $mail->hasTo('amara.nnadi@example.com');
        $matchesSale = $mail->sale->id === $sale->id;
        $matchesRef = $mail->payment->reference === $ref;

        $rendered = $mail->render();
        $hasProduct = str_contains($rendered, 'Italian Leather Loafers');
        $hasSku = str_contains($rendered, 'ATH-LF-99');
        $hasReceiptLink = str_contains($rendered, route('payment.receipt', ['sale' => $sale->id, 'reference' => $ref]));
        $hasSettled = str_contains($rendered, '100% Settled');

        return $hasTo && $matchesSale && $matchesRef && $hasProduct && $hasSku && $hasReceiptLink && $hasSettled;
    });
});

it('renders official receipt with watermark, logo image, and shipment tracking link', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-7777',
        'customer_name' => 'Emeka Okafor',
        'customer_email' => 'emeka.okafor@example.com',
        'customer_phone' => '+2348035551122',
        'destination_city' => 'Asaba',
        'destination_country' => 'Nigeria',
        'shipping_address' => 'Nnebisi Road, Asaba',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'currency' => 'NGN',
        'subtotal' => 85000.00,
        'total_amount' => 85000.00,
        'amount_paid' => 85000.00,
        'balance_due' => 0.00,
        'order_date' => now()->toDateString(),
        'paid_at' => now(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Ariaria Handmade Leather Belts (Bundle)',
        'item_sku' => 'ATH-BL-401',
        'quantity' => 20,
        'unit_price' => 4250.00,
        'total_price' => 85000.00,
    ]);

    $payment = SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 85000.00,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'paystack',
        'reference' => 'ATH-PAY-TEST-RECEIPT-REF',
        'notes' => 'Paystack card settlement',
    ]);

    $response = $this->get(route('payment.receipt', [
        'sale' => $sale->id,
        'reference' => $payment->reference,
    ]));

    $response->assertOk();

    // Verify logo image replaced the raw text
    $response->assertSee(asset('img/logo.png'));

    // Verify watermarks (central document watermark & paid watermark stamp)
    $response->assertSee('OFFICIAL RECEIPT');
    $response->assertSee('PAID VIA PAYSTACK');
    $response->assertSee('100% PRE-SHIPMENT INSPECTED');

    // Verify order and item details
    $response->assertSee('ATH-INV-2026-7777');
    $response->assertSee('Emeka Okafor');
    $response->assertSee('Ariaria Handmade Leather Belts (Bundle)');
    $response->assertSee('ATH-BL-401');

    // Verify shipment tracking live link
    $response->assertSee(route('orders.track', ['invoice' => $sale->invoice_number]));
});
