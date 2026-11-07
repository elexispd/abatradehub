<?php

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use App\Services\SalesAnalyticsService;
use Database\Seeders\StaffPermissionSeeder;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('guest cannot access sales analytics and is redirected to login', function () {
    $this->get('/admin/sales-analytics')
        ->assertRedirect('/admin/login');
});

test('super admin and sales role can access financial sales analytics', function () {
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    $this->get('/admin/sales-analytics')
        ->assertOk()
        ->assertSee('Sales &amp; Financial Analytics', escape: false)
        ->assertSee('Financial Period &amp; Market Filters', escape: false)
        ->assertSee('Gross Invoiced Sales')
        ->assertSee('Net Cash Collected')
        ->assertSee('Outstanding Balance')
        ->assertSee('Revenue &amp; Cash Inflow Trend', escape: false)
        ->assertSee('Order Pipeline &amp; Status', escape: false)
        ->assertSee('Geographic Market Distribution')
        ->assertSee('Payment Methods Settlement')
        ->assertSee('Top Selling Wholesale Products')
        ->assertSee('Staff Sales Performance')
        ->assertSee('Recent Cash Inflows &amp; Settlements', escape: false);

    // Sales role
    $salesUser = User::factory()->create(['is_active' => true]);
    $salesUser->assignRole('sales');

    $this->actingAs($salesUser);
    $this->get('/admin/sales-analytics')
        ->assertOk();
});

test('unauthorized staff role cannot access financial sales analytics', function () {
    $clerk = User::factory()->create(['is_active' => true]);
    $clerk->assignRole('inventory_clerk');

    $this->actingAs($clerk);
    $this->get('/admin/sales-analytics')
        ->assertForbidden();

    // Grant View:SalesAnalytics permission directly
    $clerk->givePermissionTo('View:SalesAnalytics');
    $this->get('/admin/sales-analytics')
        ->assertOk();
});

test('sales analytics service calculates accurate metrics across periods and countries', function () {
    $service = app(SalesAnalyticsService::class);

    $staff = User::factory()->create(['name' => 'Ngozi Sales', 'is_active' => true]);
    $staff->assignRole('sales');

    // Order 1 (This month, Nigeria): Total 150,000, Paid 100,000, Balance 50,000
    $sale1 = Sale::create([
        'customer_name' => 'Eze & Sons Trading',
        'customer_phone' => '+2348011223344',
        'destination_country' => 'Nigeria',
        'status' => SaleStatus::Processing,
        'order_date' => now()->startOfMonth()->addDays(2)->toDateString(),
        'shipping_fee' => 10000,
        'inspection_fee' => 5000,
        'created_by' => $staff->id,
    ]);

    SaleItem::create([
        'sale_id' => $sale1->id,
        'item_name' => 'Aba Made Derby Shoes',
        'item_sku' => 'DERBY-001',
        'quantity' => 10,
        'unit_price' => 13500, // 135,000 + 15,000 fees = 150,000
    ]);
    $sale1->recalculateTotals(save: true);

    SalePayment::create([
        'sale_id' => $sale1->id,
        'amount' => 100000,
        'payment_date' => now()->startOfMonth()->addDays(2)->toDateString(),
        'payment_method' => 'bank_transfer',
        'reference' => 'NIP-TEST-001',
        'recorded_by' => $staff->id,
    ]);
    $sale1->recalculateTotals(save: true);

    // Order 2 (This month, Ghana export): Total 200,000, Paid 200,000, Completed
    $sale2 = Sale::create([
        'customer_name' => 'Accra Wholesale Hub',
        'customer_phone' => '+233241112233',
        'destination_country' => 'Ghana',
        'status' => SaleStatus::Completed,
        'order_date' => now()->startOfMonth()->addDays(4)->toDateString(),
        'shipping_fee' => 20000,
        'inspection_fee' => 0,
        'created_by' => $staff->id,
    ]);

    SaleItem::create([
        'sale_id' => $sale2->id,
        'item_name' => 'Ariaria Leather Belts',
        'item_sku' => 'BELT-002',
        'quantity' => 20,
        'unit_price' => 9000, // 180,000 + 20,000 shipping = 200,000
    ]);
    $sale2->recalculateTotals(save: true);

    SalePayment::create([
        'sale_id' => $sale2->id,
        'amount' => 200000,
        'payment_date' => now()->startOfMonth()->addDays(4)->toDateString(),
        'payment_method' => 'cash',
        'reference' => 'CASH-REC-002',
        'recorded_by' => $staff->id,
    ]);
    $sale2->recalculateTotals(save: true);

    // Overall metrics for this month
    $metrics = $service->getFinancialMetrics(['period' => 'this_month']);

    expect((float) $metrics['gross_revenue'])->toBe(350000.0);
    expect((float) $metrics['net_cash_collected'])->toBe(300000.0);
    expect((float) $metrics['outstanding_balance'])->toBe(50000.0);
    expect($metrics['total_orders'])->toBe(2);
    expect($metrics['completed_orders'])->toBe(1);
    expect((float) $metrics['average_order_value'])->toBe(175000.0);
    expect((float) $metrics['value_added_services'])->toBe(35000.0); // 10k + 5k + 20k

    // Filter by country = Ghana
    $ghanaMetrics = $service->getFinancialMetrics([
        'period' => 'this_month',
        'destination_country' => 'Ghana',
    ]);
    expect((float) $ghanaMetrics['gross_revenue'])->toBe(200000.0);
    expect((float) $ghanaMetrics['net_cash_collected'])->toBe(200000.0);
    expect((float) $ghanaMetrics['outstanding_balance'])->toBe(0.0);
    expect($ghanaMetrics['total_orders'])->toBe(1);

    // Filter by country = Nigeria
    $nigeriaMetrics = $service->getFinancialMetrics([
        'period' => 'this_month',
        'destination_country' => 'Nigeria',
    ]);
    expect((float) $nigeriaMetrics['gross_revenue'])->toBe(150000.0);
    expect((float) $nigeriaMetrics['outstanding_balance'])->toBe(50000.0);

    // Chart data checks
    $trend = $service->getRevenueTrendChartData(['period' => 'this_month']);
    expect($trend)->toHaveKeys(['datasets', 'labels']);
    expect(count($trend['datasets']))->toBe(2);

    $countryData = $service->getDestinationCountryChartData(['period' => 'this_month']);
    expect($countryData['labels'])->toContain('Nigeria');
    expect($countryData['labels'])->toContain('Ghana');

    $methods = $service->getPaymentMethodChartData(['period' => 'this_month']);
    expect($methods['labels'])->toContain('Bank Transfer');
    expect($methods['labels'])->toContain('Cash');

    // Top products
    $topProducts = $service->getTopProducts(['period' => 'this_month']);
    expect($topProducts->count())->toBe(2);
    expect($topProducts->first()->item_name)->toBe('Ariaria Leather Belts');

    // Staff performance
    $staffPerf = $service->getStaffPerformance(['period' => 'this_month']);
    expect($staffPerf->count())->toBe(1);
    expect($staffPerf->first()['name'])->toBe('Ngozi Sales');
    expect($staffPerf->first()['orders_count'])->toBe(2);
});

test('sales analytics livewire page can mount with filters form', function () {
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    \Livewire\Livewire::test(\App\Filament\Pages\SalesAnalytics::class)
        ->assertSuccessful()
        ->assertSchemaExists('filtersForm');
});

test('financial stats widget renders with custom period filters', function () {
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    $sale = Sale::create([
        'customer_name' => 'Kano Leather Merchants',
        'customer_phone' => '+2348030001122',
        'destination_country' => 'Nigeria',
        'order_date' => now()->toDateString(),
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'item_name' => 'Wholesale Mules',
        'quantity' => 15,
        'unit_price' => 10000,
    ]);
    $sale->recalculateTotals(save: true);

    \Livewire\Livewire::test(\App\Filament\Pages\SalesAnalytics\Widgets\FinancialStatsOverview::class, [
        'pageFilters' => [
            'period' => 'today',
        ],
    ])
        ->assertSuccessful()
        ->assertSee('Gross Invoiced Sales')
        ->assertSee('₦150,000.00');
});

test('top products and staff performance tables render without full group by conflicts', function () {
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    \Livewire\Livewire::test(\App\Filament\Pages\SalesAnalytics\Widgets\TopProductsTable::class)
        ->assertSuccessful();

    \Livewire\Livewire::test(\App\Filament\Pages\SalesAnalytics\Widgets\StaffSalesPerformanceTable::class)
        ->assertSuccessful();
});
