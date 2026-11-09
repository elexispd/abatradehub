<?php

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Database\Seeders\StaffPermissionSeeder;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('it forbids unauthorized direct access to /orders/{id}/receipt without reference or signature', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0001',
        'customer_name' => 'Chioma Okeke',
        'customer_phone' => '+2348011223344',
        'destination_city' => 'Enugu',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'total_amount' => 50000.00,
        'amount_paid' => 50000.00,
        'balance_due' => 0.00,
    ]);

    // Direct access by guessing integer ID e.g. /orders/1/receipt
    $response = $this->get(route('payment.receipt', ['sale' => $sale->id]));
    $response->assertForbidden();

    // Access with forged reference
    $responseForged = $this->get(route('payment.receipt', [
        'sale' => $sale->id,
        'reference' => 'FORGED-PAYSTACK-REF-12345',
    ]));
    $responseForged->assertForbidden();
});

test('it allows access to /orders/{id}/receipt with a valid Paystack payment reference', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0002',
        'customer_name' => 'Emeka Okafor',
        'customer_phone' => '+2348099887766',
        'destination_city' => 'Onitsha',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'total_amount' => 75000.00,
        'amount_paid' => 75000.00,
        'balance_due' => 0.00,
    ]);

    $payment = SalePayment::create([
        'sale_id' => $sale->id,
        'payment_method' => 'paystack',
        'reference' => 'ATH-PAY-GENUINE-REF-9988',
        'amount' => 75000.00,
        'payment_date' => now()->toDateString(),
    ]);

    $response = $this->get(route('payment.receipt', [
        'sale' => $sale->id,
        'reference' => $payment->reference,
    ]));

    $response->assertOk();
    $response->assertSee('ATH-INV-2026-0002');
    $response->assertSee('Emeka Okafor');
});

test('it allows access to /orders/{id}/receipt with a valid cryptographic signed URL', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0003',
        'customer_name' => 'Zainab Danjuma',
        'customer_phone' => '+2348123334455',
        'destination_city' => 'Kaduna',
        'destination_country' => 'Nigeria',
    ]);

    $signedUrl = URL::signedRoute('payment.receipt', ['sale' => $sale->id]);

    $response = $this->get($signedUrl);
    $response->assertOk();
    $response->assertSee('ATH-INV-2026-0003');
    $response->assertSee('Zainab Danjuma');
});

test('it allows authenticated staff with permission to view any receipt', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0004',
        'customer_name' => 'Femi Adeyemi',
        'customer_phone' => '+2348034445566',
        'destination_city' => 'Ibadan',
        'destination_country' => 'Nigeria',
    ]);

    $staff = User::factory()->create();
    $staffRole = Role::findByName('sales');
    $staff->assignRole($staffRole);

    $response = $this->actingAs($staff)->get(route('payment.receipt', ['sale' => $sale->id]));
    $response->assertOk();
    $response->assertSee('ATH-INV-2026-0004');
});

test('it forbids unauthorized access to /payment/success/{id} without matching reference', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0005',
        'customer_name' => 'Ngozi Okonkwo',
        'customer_phone' => '+2348177778899',
        'destination_city' => 'Abuja',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
    ]);

    $payment = SalePayment::create([
        'sale_id' => $sale->id,
        'payment_method' => 'paystack',
        'reference' => 'ATH-PAY-SUCCESS-VALID-REF',
        'amount' => 40000.00,
        'payment_date' => now()->toDateString(),
    ]);

    // Unauthorized access: integer guessing /payment/success/1 without reference
    $this->get(route('payment.success', ['sale' => $sale->id]))
        ->assertForbidden();

    // Access with forged reference
    $this->get(route('payment.success', ['sale' => $sale->id, 'reference' => 'FORGED-REF']))
        ->assertForbidden();

    // Legitimate access with matching reference
    $this->get(route('payment.success', ['sale' => $sale->id, 'reference' => $payment->reference]))
        ->assertOk()
        ->assertSee('ATH-INV-2026-0005');
});

test('it prevents IDOR invoice leakage on /orders/{id}/track for unauthenticated visitors', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0006',
        'customer_name' => 'Private Buyer',
        'customer_phone' => '+2348000001122',
        'destination_city' => 'Owerri',
        'destination_country' => 'Nigeria',
    ]);

    // Guessing integer /orders/6/track without reference -> redirects to blank /track-order without leaking invoice!
    $this->get(route('orders.track.sale', ['sale' => $sale->id]))
        ->assertRedirect(route('orders.track'));

    // Authorized staff -> allowed to redirect to track with invoice
    $staff = User::factory()->create();
    $staff->assignRole(Role::findByName('super_admin'));

    $this->actingAs($staff)
        ->get(route('orders.track.sale', ['sale' => $sale->id]))
        ->assertRedirect(route('orders.track', ['invoice' => 'ATH-INV-2026-0006']));
});

