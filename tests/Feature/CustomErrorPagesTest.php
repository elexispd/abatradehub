<?php

use App\Models\Sale;
use Database\Seeders\StaffPermissionSeeder;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('it renders the custom branded 404 page when a route is not found and never leaks model query exceptions', function () {
    $response = $this->get('/non-existent-page-or-product-slug');

    $response->assertNotFound();
    $response->assertSee('404');
    $response->assertSee('Find That Page');
    $response->assertSee('Explore Wholesale Catalog');
    $response->assertSee('Track an Order');
    $response->assertSee(route('products.index'));
    $response->assertSee(route('orders.track'));

    // Trigger ModelNotFoundException on route model binding e.g. non-existent sale ID 59
    $modelResponse = $this->get('/orders/59/receipt');
    $modelResponse->assertNotFound();
    $modelResponse->assertDontSee('No query results for model');
    $modelResponse->assertDontSee('App\Models\Sale');
    $modelResponse->assertSee('Find That Page');
});

test('it renders the custom branded 403 page without exposing staff or administrative information', function () {
    $sale = Sale::create([
        'invoice_number' => 'ATH-INV-2026-9991',
        'customer_name' => 'Restricted Buyer',
        'customer_phone' => '+2348011223344',
        'destination_city' => 'Warri',
        'destination_country' => 'Nigeria',
    ]);

    // Unauthorized receipt access triggers 403
    $response = $this->get(route('payment.receipt', ['sale' => $sale->id]));

    $response->assertForbidden();
    $response->assertSee('403');
    $response->assertSee('Access to This Resource is Restricted');
    $response->assertSee('Looking for Your Official Receipt?');
    $response->assertSee('Track Order &amp; Verify Identity', false);
    $response->assertSee(route('orders.track'));

    // Verify staff/admin details are NOT exposed
    $response->assertDontSee('Staff / Administrative Access');
    $response->assertDontSee('Admin Portal');
});
