<?php

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Livewire\TrackOrder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Database\Seeders\StaffPermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('it renders the track order page at /track-order and /track', function () {
    $this->get('/track-order')
        ->assertOk()
        ->assertSeeLivewire(TrackOrder::class)
        ->assertSee('Track Your Wholesale Order');

    $this->get('/track')
        ->assertRedirect(route('orders.track'));
});

test('it displays initial guidance before any search', function () {
    Livewire::test(TrackOrder::class)
        ->assertSee('Track Your Wholesale Order')
        ->assertSee('Find Your Invoice Code')
        ->assertSee('100% Pre-Shipment QC')
        ->assertSee('Park Waybill & Delivery')
        ->assertDontSee('Fulfillment & Quality Inspection Pipeline');
});

test('it validates and displays error when invoice is empty or invalid', function () {
    Livewire::test(TrackOrder::class)
        ->set('invoice', '')
        ->call('track')
        ->assertSee('Please enter an invoice or order number')
        ->assertSet('sale', null);

    Livewire::test(TrackOrder::class)
        ->set('invoice', 'ATH-INV-9999-9999')
        ->call('track')
        ->assertSee('No wholesale order was found matching "ATH-INV-9999-9999"')
        ->assertSet('sale', null);
});

test('it tracks an existing sale by exact invoice number and displays fulfillment stages and items', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'name' => 'Ariaria Handcrafted Oxford Leather Shoes',
        'sku' => 'ATH-SH-8821',
        'base_price' => 12500,
    ]);

    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0042',
        'customer_name' => 'Chinedu Eze',
        'customer_phone' => '+2348031234567',
        'customer_email' => 'chinedu.eze@example.com',
        'customer_company' => 'Eze Brothers Boutique',
        'destination_city' => 'Port Harcourt',
        'destination_country' => 'Nigeria',
        'shipping_address' => 'Plot 5, Trans-Amadi Industrial Layout',
        'status' => SaleStatus::Inspection,
        'payment_status' => PaymentStatus::Paid,
        'currency' => 'NGN',
        'subtotal' => 125000.00,
        'total_amount' => 125000.00,
        'amount_paid' => 125000.00,
        'balance_due' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'item_name' => $product->name,
        'item_sku' => $product->sku,
        'quantity' => 10,
        'unit_price' => 12500.00,
        'total_price' => 125000.00,
    ]);

    \App\Models\SalePayment::create([
        'sale_id' => $sale->id,
        'payment_method' => 'paystack',
        'payment_reference' => 'ATH-PSTK-TEST-99',
        'amount' => 125000.00,
        'payment_date' => now()->toDateString(),
    ]);

    // Unverified public search: PII is masked, receipt locked
    Livewire::test(TrackOrder::class, ['initialInvoice' => 'ATH-INV-2026-0042'])
        ->assertSet('invoice', 'ATH-INV-2026-0042')
        ->assertSet('isVerified', false)
        ->assertSee('ATH-INV-2026-0042')
        ->assertSee('Chinedu E***')
        ->assertSee('Buyer identity masked for privacy')
        ->assertSee('Port Harcourt, Nigeria')
        ->assertSee('Ariaria Handcrafted Oxford Leather Shoes')
        ->assertSee('ATH-SH-8821')
        ->assertSee('100% Quality Inspection')
        ->assertSee('Fulfillment & Quality Inspection Pipeline')
        ->assertSee('Chat on WhatsApp')
        ->assertSee('Verify Phone to View Receipt');

    // Verified search with matching phone: full PII and signed receipt unlocked
    Livewire::test(TrackOrder::class, [
        'initialInvoice' => 'ATH-INV-2026-0042',
        'initialPhone' => '+2348031234567',
    ])
        ->assertSet('isVerified', true)
        ->assertSee('Chinedu Eze')
        ->assertSee('Plot 5, Trans-Amadi Industrial Layout')
        ->assertSee('Verified')
        ->assertSee('View Official Receipt');
});

test('it tracks an existing sale with numeric suffix (e.g. 0042)', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0042',
        'customer_name' => 'Chinedu Eze',
        'customer_phone' => '+2348031234567',
        'destination_city' => 'Kano',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Dispatched,
        'payment_status' => PaymentStatus::Paid,
        'currency' => 'NGN',
        'subtotal' => 50000.00,
        'total_amount' => 50000.00,
        'amount_paid' => 50000.00,
        'balance_due' => 0.00,
    ]);

    Livewire::test(TrackOrder::class)
        ->set('invoice', '0042')
        ->call('track')
        ->assertSet('sale.id', $sale->id)
        ->assertSee('ATH-INV-2026-0042')
        ->assertSee('Dispatched & In Transit');
});

test('it verifies optional phone number matching and rejects mismatching phone', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0077',
        'customer_name' => 'Fatima Bello',
        'customer_phone' => '+2348129998877',
        'customer_email' => 'fatima.bello@example.com',
        'destination_city' => 'Abuja',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'currency' => 'NGN',
        'subtotal' => 45000.00,
        'total_amount' => 45000.00,
        'amount_paid' => 45000.00,
        'balance_due' => 0.00,
    ]);

    // Matching phone ends-with digits
    Livewire::test(TrackOrder::class)
        ->set('invoice', 'ATH-INV-2026-0077')
        ->set('phone', '08129998877')
        ->call('track')
        ->assertSet('sale.id', $sale->id)
        ->assertSee('Fatima Bello');

    // Matching email
    Livewire::test(TrackOrder::class)
        ->set('invoice', 'ATH-INV-2026-0077')
        ->set('phone', 'fatima.bello@example.com')
        ->call('track')
        ->assertSet('sale.id', $sale->id)
        ->assertSee('Fatima Bello');

    // Mismatching phone
    Livewire::test(TrackOrder::class)
        ->set('invoice', 'ATH-INV-2026-0077')
        ->set('phone', '08000000000')
        ->call('track')
        ->assertSet('sale', null)
        ->assertSee('does not match our records for this order');
});

test('it resets search state cleanly', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0088',
        'customer_name' => 'Obinna Okafor',
        'customer_phone' => '+2348033334444',
        'destination_city' => 'Enugu',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Delivered,
    ]);

    Livewire::test(TrackOrder::class, ['initialInvoice' => 'ATH-INV-2026-0088'])
        ->assertSet('sale.id', $sale->id)
        ->call('resetSearch')
        ->assertSet('sale', null)
        ->assertSet('invoice', '')
        ->assertSet('searched', false)
        ->assertSee('Find Your Invoice Code');
});

test('it redirects from /orders/{sale}/track safely without leaking invoice numbers to guests', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0099',
        'customer_name' => 'Ngozi Okon',
        'customer_phone' => '+2348011223344',
        'destination_city' => 'Calabar',
        'destination_country' => 'Nigeria',
    ]);

    // Unauthenticated guest without reference -> safe redirect to track page without leaking invoice
    $this->get(route('orders.track.sale', $sale))
        ->assertRedirect(route('orders.track'));
});
