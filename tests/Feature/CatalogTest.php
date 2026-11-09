<?php

use App\Livewire\CatalogEnquiryModal;
use App\Livewire\ProductCatalog;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\PriceTier;
use App\Models\Product;
use Livewire\Livewire;

it('renders the catalog page at /products', function () {
    $category = Category::factory()->create(['name' => 'Linen Sets', 'slug' => 'linen-sets']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Relaxed Linen Co-ord',
        'slug' => 'relaxed-linen-co-ord',
        'moq' => 10,
        'base_price' => 18500,
    ]);

    $this->get('/products')
        ->assertOk()
        ->assertSee('Wholesale Catalog &amp; Bulk Sourcing', false)
        ->assertSee('Linen Sets')
        ->assertSee('Relaxed Linen Co-ord')
        ->assertSee('MOQ: 10 pieces');
});

it('filters products by clean category URL /products/{category}', function () {
    $catA = Category::factory()->create(['name' => 'Senators Wear', 'slug' => 'senators-wear']);
    $catB = Category::factory()->create(['name' => 'Footwear Hub', 'slug' => 'footwear-hub']);

    $prodA = Product::factory()->for($catA)->create(['name' => 'Navy Senator Kaftan']);
    $prodB = Product::factory()->for($catB)->create(['name' => 'Leather Slippers']);

    $this->get('/products/senators-wear')
        ->assertOk()
        ->assertSee('Navy Senator Kaftan')
        ->assertDontSee('Leather Slippers');
});

it('renders product detail page with clean URL /products/{category}/{product}', function () {
    $category = Category::factory()->create(['name' => 'Mens Fashion', 'slug' => 'mens-fashion']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Classic Navy Embroidered Kaftan',
        'slug' => 'classic-navy-embroidered-kaftan',
        'sku' => 'ATH-MN-1001',
        'moq' => 10,
        'base_price' => 21000,
        'lead_time_min_days' => 5,
        'lead_time_max_days' => 7,
    ]);

    PriceTier::create([
        'product_id' => $product->id,
        'min_quantity' => 10,
        'max_quantity' => 49,
        'unit_price' => 21000,
    ]);
    PriceTier::create([
        'product_id' => $product->id,
        'min_quantity' => 50,
        'max_quantity' => null,
        'unit_price' => 18500,
    ]);

    $this->get('/products/mens-fashion/classic-navy-embroidered-kaftan')
        ->assertOk()
        ->assertSee('Classic Navy Embroidered Kaftan')
        ->assertSee('ATH-MN-1001')
        ->assertSee('MOQ: 10 pieces')
        ->assertSee('Lead Time: 5-7 days')
        ->assertSee('Wholesale Volume Discount Tiers')
        ->assertSee('50+ pieces')
        ->assertSee('Submit Bulk Enquiry')
        ->assertSee('Add to Wholesale Basket');
});

it('redirects product detail to canonical category if category does not match', function () {
    $catReal = Category::factory()->create(['slug' => 'real-category']);
    $catWrong = Category::factory()->create(['slug' => 'wrong-category']);

    $product = Product::factory()->for($catReal)->create(['slug' => 'sample-tunic']);

    $this->get('/products/wrong-category/sample-tunic')
        ->assertRedirect('/products/real-category/sample-tunic');
});

it('supports live search and sorting in ProductCatalog Livewire component', function () {
    $cat = Category::factory()->create();
    Product::factory()->for($cat)->create(['name' => 'Aba Embroidered Linen Shirt', 'base_price' => 12000]);
    Product::factory()->for($cat)->create(['name' => 'Cotton Chino Shorts', 'base_price' => 8000]);

    Livewire::test(ProductCatalog::class)
        ->assertSee('Aba Embroidered Linen Shirt')
        ->assertSee('Cotton Chino Shorts')
        ->set('search', 'Embroidered')
        ->assertSee('Aba Embroidered Linen Shirt')
        ->assertDontSee('Cotton Chino Shorts')
        ->set('search', '')
        ->set('sort', 'price-low')
        ->assertSee('Aba Embroidered Linen Shirt');
});

it('submits fast wholesale enquiry from modal', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'name' => 'Relaxed Linen Co-ord',
        'sku' => 'ATH-LN-001',
        'moq' => 10,
    ]);

    // Test with named product payload
    Livewire::test(CatalogEnquiryModal::class)
        ->dispatch('open-enquiry-modal', product: $product->toQuickViewArray())
        ->assertSet('isOpen', true)
        ->assertSet('productName', 'Relaxed Linen Co-ord')
        ->assertSet('quantity', 10);

    // Test with direct array payload
    Livewire::test(CatalogEnquiryModal::class)
        ->dispatch('open-enquiry-modal', $product->toQuickViewArray())
        ->assertSet('isOpen', true)
        ->assertSet('productName', 'Relaxed Linen Co-ord')
        ->assertSet('quantity', 10)
        ->assertSet('country', 'Nigeria')
        ->set('name', 'Chinedu')
        ->set('phone', '+234 803 123 4567')
        ->set('country', 'Ghana')
        ->set('quantity', 20)
        ->set('destination', 'Makola Market, Accra')
        ->set('notes', '10 Navy, 10 Cream')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $savedEnquiry = Enquiry::where('name', 'Chinedu')->first();
    expect($savedEnquiry)
        ->not->toBeNull()
        ->country->toBe('Ghana')
        ->city->toBe('Makola Market, Accra')
        ->source->toBe('catalog');

    expect($product->fresh()->enquiryItems)->toHaveCount(1);
    expect($product->fresh()->enquiryItems->first()->enquiry_id)->toBe($savedEnquiry->id);
    expect($savedEnquiry->type_label)->toBe('Product Enquiry');
    expect($savedEnquiry->subject)->toContain('Relaxed Linen Co-ord (20 pcs)');
});

