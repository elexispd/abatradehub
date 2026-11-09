<?php

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Database\Seeders\StaffPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('it generates standard invoice numbers sequentially', function () {
    $year = now()->year;
    $inv1 = Sale::generateInvoiceNumber();
    expect($inv1)->toBe("ATH-INV-{$year}-0001");

    $sale1 = Sale::create([
        'customer_name' => 'Chioma Adebayo',
        'customer_phone' => '+2348011122233',
        'destination_country' => 'Nigeria',
    ]);
    expect($sale1->invoice_number)->toBe("ATH-INV-{$year}-0001");

    $inv2 = Sale::generateInvoiceNumber();
    expect($inv2)->toBe("ATH-INV-{$year}-0002");

    $sale2 = Sale::create([
        'customer_name' => 'Kofi Mensah',
        'customer_phone' => '+233201234567',
        'destination_country' => 'Ghana',
    ]);
    expect($sale2->invoice_number)->toBe("ATH-INV-{$year}-0002");
});

test('it calculates totals, shipping, inspection, and discounts', function () {
    $sale = Sale::create([
        'customer_name' => 'Nkechi Boutique',
        'customer_phone' => '+2348099988776',
        'destination_country' => 'Nigeria',
        'shipping_fee' => 15000,
        'inspection_fee' => 5000,
        'discount_amount' => 2000,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Handmade Leather Loafers',
        'quantity' => 10,
        'unit_price' => 12000, // 120,000
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Bespoke Senator Fabric (4 yards)',
        'quantity' => 5,
        'unit_price' => 8000, // 40,000
    ]);

    $sale->refresh();

    // Subtotal: 120,000 + 40,000 = 160,000
    expect((float) $sale->subtotal)->toBe(160000.0);
    // Total: 160,000 + 15,000 (shipping) + 5,000 (inspection) - 2,000 (discount) = 178,000
    expect((float) $sale->total_amount)->toBe(178000.0);
    expect((float) $sale->amount_paid)->toBe(0.0);
    expect((float) $sale->balance_due)->toBe(178000.0);
    expect($sale->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('it tracks multiple installment payments and updates payment status accordingly', function () {
    $sale = Sale::create([
        'customer_name' => 'Ibrahim Cisse',
        'customer_phone' => '+2250102030405',
        'destination_country' => "Cote d'Ivoire",
        'shipping_fee' => 25000,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Ariaria Quality Oxford Shoes',
        'quantity' => 20,
        'unit_price' => 10000, // 200,000
    ]);

    $sale->refresh();
    // Total: 200,000 + 25,000 = 225,000
    expect((float) $sale->total_amount)->toBe(225000.0);
    expect($sale->payment_status)->toBe(PaymentStatus::Unpaid);

    // Installment 1: Initial Deposit 60% (135,000)
    SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 135000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'bank_transfer',
        'reference' => 'NIP-TXN-12345',
        'notes' => '1st Installment (60% deposit)',
    ]);

    $sale->refresh();
    expect((float) $sale->amount_paid)->toBe(135000.0);
    expect((float) $sale->balance_due)->toBe(90000.0);
    expect($sale->payment_status)->toBe(PaymentStatus::Partial);
    expect($sale->paid_at)->toBeNull();

    // Installment 2: Final Balance (90,000)
    SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 90000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'bank_transfer',
        'reference' => 'NIP-TXN-67890',
        'notes' => 'Final balance payment before dispatch',
    ]);

    $sale->refresh();
    expect((float) $sale->amount_paid)->toBe(225000.0);
    expect((float) $sale->balance_due)->toBe(0.0);
    expect($sale->payment_status)->toBe(PaymentStatus::Paid);
    expect($sale->paid_at)->not->toBeNull();
});

test('sales role can view sales and invoice, while unauthorized role is forbidden', function () {
    $sale = Sale::create([
        'customer_name' => 'Amina Bello',
        'customer_phone' => '+2348033344455',
        'destination_country' => 'Nigeria',
    ]);

    // 1. Sales rep
    $salesUser = User::factory()->create(['is_active' => true]);
    $salesUser->assignRole('sales');

    $admin = fn (string $path = '') => '/' . \App\Support\Admin::path($path);

    $this->actingAs($salesUser);
    $this->get($admin('sales'))->assertOk();
    $this->get($admin("sales/{$sale->id}/edit"))->assertOk();
    $this->get($admin("sales/{$sale->id}/invoice"))
        ->assertOk()
        ->assertSee($sale->invoice_number)
        ->assertSee('Amina Bello')
        ->assertSee(config('abatradehub.bank.account_number'));

    // 2. Inventory clerk (cannot access sales)
    $clerk = User::factory()->create(['is_active' => true]);
    $clerk->assignRole('inventory_clerk');

    $this->actingAs($clerk);
    $this->get($admin('sales'))->assertForbidden();
    $this->get($admin("sales/{$sale->id}/edit"))->assertForbidden();
    $this->get($admin("sales/{$sale->id}/invoice"))->assertForbidden();
});

test('it converts an enquiry with items into a sale with invoice', function () {
    $enquiry = Enquiry::create([
        'name' => 'Emeka Okafor',
        'phone' => '+2348077766554',
        'email' => 'emeka@example.com',
        'company' => 'Okafor Brothers Ltd',
        'country' => 'Nigeria',
        'city' => 'Onitsha',
        'message' => 'Need 50 pairs of suede Chelsea boots',
    ]);

    $category = \App\Models\Category::create(['name' => 'Footwear', 'slug' => 'footwear']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Suede Chelsea Boots',
        'slug' => 'suede-chelsea-boots',
        'sku' => 'BOOT-SUEDE-01',
        'base_price' => 14000,
        'status' => 'published',
    ]);

    EnquiryItem::create([
        'enquiry_id' => $enquiry->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'quantity' => 50,
        'note' => 'Size 42-45 mix',
    ]);

    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    // Call the convert action logic
    $sale = Sale::create([
        'enquiry_id' => $enquiry->id,
        'customer_name' => $enquiry->name,
        'customer_phone' => $enquiry->phone,
        'customer_email' => $enquiry->email,
        'customer_company' => $enquiry->company,
        'destination_country' => $enquiry->country,
        'destination_city' => $enquiry->city,
        'order_date' => now()->toDateString(),
        'currency' => 'NGN',
    ]);

    foreach ($enquiry->items as $item) {
        $unitPrice = (float) ($item->product?->base_price ?: 0);
        $sale->items()->create([
            'product_id' => $item->product_id,
            'item_name' => $item->product_name,
            'item_sku' => $item->product_sku,
            'quantity' => $item->quantity,
            'unit_price' => $unitPrice,
            'total_price' => $item->quantity * $unitPrice,
            'specifications' => $item->note,
        ]);
    }
    $sale->recalculateTotals(save: true);

    expect($sale->enquiry_id)->toBe($enquiry->id);
    expect($sale->customer_name)->toBe('Emeka Okafor');
    expect((float) $sale->total_amount)->toBe(700000.0);
    expect($sale->items()->count())->toBe(1);
    expect($sale->invoice_number)->toStartWith('ATH-INV-');
});

test('create sale page renders with spacious layout and catalog selection', function () {
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    $this->get('/' . \App\Support\Admin::path('sales/create'))
        ->assertOk()
        ->assertSee('Order & Customer Details')
        ->assertSee('Products & Line Items')
        ->assertSee('Select from Catalog')
        ->assertSee('Live Financial Summary');
});

test('payments can be completed at once in full', function () {
    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole('sales');
    $this->actingAs($staff);

    $sale = Sale::create([
        'customer_name' => 'Fatima Aliyu',
        'customer_phone' => '+2348055566677',
        'destination_country' => 'Nigeria',
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Wholesale Ankara Bundle',
        'quantity' => 10,
        'unit_price' => 25000,
    ]);

    $sale->refresh();
    expect((float) $sale->total_amount)->toBe(250000.0);
    expect($sale->payment_status)->toBe(PaymentStatus::Unpaid);

    // Complete full payment at once
    SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => $sale->balance_due,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'bank_transfer',
        'reference' => 'NIP-FULL-998877',
        'notes' => 'Full payment completed at once',
    ]);

    $sale->refresh();
    expect((float) $sale->amount_paid)->toBe(250000.0);
    expect((float) $sale->balance_due)->toBe(0.0);
    expect($sale->payment_status)->toBe(PaymentStatus::Paid);
});

test('a paid sale or sale with payments cannot be deleted', function () {
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    $sale = Sale::create([
        'customer_name' => 'Chidi Obi',
        'customer_phone' => '+2348011223344',
        'destination_country' => 'Nigeria',
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Leather Bags',
        'quantity' => 5,
        'unit_price' => 10000,
    ]);

    $payment = SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 50000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'cash',
    ]);

    $sale->refresh();
    expect($sale->payment_status)->toBe(PaymentStatus::Paid);

    // Policy check: delete must be disallowed
    $policy = new \App\Policies\SalePolicy();
    expect($policy->delete($superAdmin, $sale))->toBeFalse();
    expect($policy->forceDelete($superAdmin, $sale))->toBeFalse();

    // Model check: attempting delete throws DomainException
    expect(fn () => $sale->delete())->toThrow(\DomainException::class);

    // Payment delete check: payment on a paid sale cannot be deleted
    expect(fn () => $payment->delete())->toThrow(\DomainException::class);
});

test('all staff sales activity is recorded in audit logs and user stamps', function () {
    $staff1 = User::factory()->create(['name' => 'Kelechi Sales', 'is_active' => true]);
    $staff1->assignRole('sales');

    $this->actingAs($staff1);

    $sale = Sale::create([
        'customer_name' => 'Amaka Textiles',
        'customer_phone' => '+2348088899900',
        'destination_country' => 'Nigeria',
    ]);

    expect($sale->created_by)->toBe($staff1->id);
    expect($sale->updated_by)->toBe($staff1->id);

    // Staff 2 edits the sale
    $staff2 = User::factory()->create(['name' => 'Emeka Manager', 'is_active' => true]);
    $staff2->assignRole('sales');

    $this->actingAs($staff2);
    $sale->update([
        'status' => SaleStatus::Processing,
        'customer_company' => 'Amaka & Sons Enterprise',
    ]);

    $sale->refresh();
    expect($sale->created_by)->toBe($staff1->id);
    expect($sale->updated_by)->toBe($staff2->id);

    // Verify audit log entries exist for this sale
    $auditLogs = \App\Models\AuditLog::where('auditable_type', Sale::class)
        ->where('auditable_id', $sale->id)
        ->get();

    expect($auditLogs->count())->toBeGreaterThanOrEqual(1);

    // Record a payment and check payment audit log
    SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 15000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'bank_transfer',
        'reference' => 'TEST-AUDIT-REF',
    ]);

    $paymentLog = \App\Models\AuditLog::where('auditable_type', Sale::class)
        ->where('auditable_id', $sale->id)
        ->where('action', 'payment_recorded')
        ->first();

    expect($paymentLog)->not->toBeNull();
    expect($paymentLog->user_id)->toBe($staff2->id);
    expect($paymentLog->description)->toContain('Emeka Manager recorded payment');
});

test('when order is marked completed, no one else can reverse it aside super admin and it logs it', function () {
    $superAdmin = User::factory()->admin()->create(['name' => 'Root Super Admin']);
    $salesStaff = User::factory()->create(['name' => 'Nnamdi Sales', 'is_active' => true]);
    $salesStaff->assignRole('sales');

    // 1. Create a sale and mark it completed
    $this->actingAs($salesStaff);
    $sale = Sale::create([
        'customer_name' => 'Ogochukwu Stores',
        'customer_phone' => '+2348019998888',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Completed,
    ]);

    expect($sale->isCompleted())->toBeTrue();

    // 2. Non-super admin staff attempts to reverse order status to processing or draft -> must fail
    expect(function () use ($sale) {
        $sale->update(['status' => SaleStatus::Processing]);
    })->toThrow(\DomainException::class, 'A completed order can only be reversed by a Super Admin.');

    $sale->refresh();
    expect($sale->status)->toBe(SaleStatus::Completed);

    // 3. Super admin reverses the completed order with a reason
    $this->actingAs($superAdmin);

    $sale->reversal_reason = 'Customer requested adding 20 more units of shoes';
    $sale->update([
        'status' => SaleStatus::Processing,
    ]);

    $sale->refresh();
    expect($sale->status)->toBe(SaleStatus::Processing);

    // 4. Verify dedicated audit log was created for the reversal
    $reversalLog = \App\Models\AuditLog::where('auditable_type', Sale::class)
        ->where('auditable_id', $sale->id)
        ->where('action', 'order_reversed')
        ->first();

    expect($reversalLog)->not->toBeNull();
    expect($reversalLog->user_id)->toBe($superAdmin->id);
    expect($reversalLog->user_name)->toBe('Root Super Admin');
    expect($reversalLog->description)->toContain('Super Admin Root Super Admin reversed completed order');
    expect($reversalLog->description)->toContain('Reason: Customer requested adding 20 more units of shoes');
    expect($reversalLog->old_values['status'])->toBe('completed');
    expect($reversalLog->new_values['status'])->toBe('processing');
    expect($reversalLog->new_values['reason'])->toBe('Customer requested adding 20 more units of shoes');
});

it('allows staff to edit order status on fully paid online sales without payment method validation errors', function () {
    $superAdmin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin-paystack-edit@test.com',
        'is_active' => true,
    ]);
    $role = Role::findOrCreate('super_admin');
    $superAdmin->assignRole($role);

    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-0005',
        'customer_name' => 'Promise Elendu Ogbonnaya',
        'customer_phone' => '+2348000000005',
        'customer_email' => 'promise@example.com',
        'destination_country' => 'Nigeria',
        'destination_city' => 'Lagos',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'currency' => 'NGN',
        'subtotal' => 6800.00,
        'total_amount' => 6800.00,
        'amount_paid' => 6800.00,
        'balance_due' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Handmade Leather Slides',
        'quantity' => 1,
        'unit_price' => 6800.00,
        'total_price' => 6800.00,
    ]);

    SalePayment::create([
        'sale_id' => $sale->id,
        'amount' => 6800.00,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'paystack',
        'reference' => 'ATH-PAY-5-1794134995-29gaw',
        'notes' => 'Online payment verified via Paystack (card)',
    ]);

    $this->actingAs($superAdmin);

    // Test editing sale status via Filament EditSale component
    Livewire::test(\App\Filament\Resources\Sales\Pages\EditSale::class, ['record' => $sale->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('100% Fully Settled Online via Paystack')
        ->assertSee('ATH-PAY-5-1794134995-29gaw')
        ->assertSee('Order Placed')
        ->assertSee('Payment Confirmed')
        ->assertSee('Promise Elendu Ogbonnaya')
        ->assertSee('Online Web Order')
        ->assertDontSee('+ Add Another Installment Payment')
        ->fillForm([
            'status' => SaleStatus::Inspection->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $sale->refresh();
    expect($sale->status)->toBe(SaleStatus::Inspection)
        ->and($sale->payment_status)->toBe(PaymentStatus::Paid)
        ->and($sale->amount_paid)->toEqual(6800.00);
});

it('sends a thank you email to the customer when the order is completed if an email is provided', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'name' => 'Senator Kaftan Special Edition',
        'sku' => 'ATH-SN-999',
        'base_price' => 35000,
    ]);

    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-8888',
        'customer_name' => 'Kalu Uche',
        'customer_phone' => '+2348039991122',
        'customer_email' => 'kalu.uche@example.com',
        'destination_country' => 'Nigeria',
        'destination_city' => 'Abuja',
        'status' => SaleStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'currency' => 'NGN',
        'subtotal' => 70000.00,
        'total_amount' => 70000.00,
        'amount_paid' => 70000.00,
        'balance_due' => 0.00,
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'item_name' => $product->name,
        'item_sku' => $product->sku,
        'quantity' => 2,
        'unit_price' => 35000.00,
        'total_price' => 70000.00,
    ]);

    // Ensure no email sent while in processing
    \Illuminate\Support\Facades\Mail::assertNothingSent();

    // Mark order as completed
    $sale->status = SaleStatus::Completed;
    $sale->save();

    // Assert that thank you email was dispatched to customer email
    \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OrderCompletedThankYouMail::class, function ($mail) use ($sale) {
        $hasTo = $mail->hasTo('kalu.uche@example.com');
        $matchesSale = $mail->sale->id === $sale->id;

        $rendered = $mail->render();
        $hasThankYou = str_contains($rendered, 'Thank You for Your Wholesale Order');
        $hasInvoice = str_contains($rendered, 'ATH-INV-2026-8888');
        $hasProduct = str_contains($rendered, 'Senator Kaftan Special Edition');
        $hasReorderLink = str_contains($rendered, route('products.index'));

        return $hasTo && $matchesSale && $hasThankYou && $hasInvoice && $hasProduct && $hasReorderLink;
    });

    // Test that an order without customer email does not throw and sends nothing
    \Illuminate\Support\Facades\Mail::fake();
    $saleNoEmail = Sale::create([
        'invoice_number' => 'ATH-INV-2026-8889',
        'customer_name' => 'Walk-in Buyer',
        'customer_phone' => '+2348000000000',
        'customer_email' => null,
        'status' => SaleStatus::Processing,
    ]);

    $saleNoEmail->status = SaleStatus::Completed;
    $saleNoEmail->save();

    \Illuminate\Support\Facades\Mail::assertNothingSent();
});
