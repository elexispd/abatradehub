<?php

use App\Models\Category;
use App\Models\Product;

it('serves a valid robots.txt file with sitemap reference and admin disallow', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk()
        ->assertSee('User-agent: *')
        ->assertSee('Allow: /')
        ->assertSee('Disallow: /admin')
        ->assertSee('Disallow: /' . \App\Support\Admin::path())
        ->assertSee(url('/sitemap.xml'));
});

it('generates a valid XML sitemap including home, catalog, categories, and published products', function () {
    $category = Category::factory()->create(['name' => 'Textiles Aba', 'slug' => 'textiles-aba']);
    $publishedProduct = Product::factory()->for($category)->create([
        'name' => 'Ankara Wax Fabric Bundle',
        'slug' => 'ankara-wax-fabric-bundle',
    ]);

    $draftCategory = Category::factory()->create(['slug' => 'draft-category']);
    $draftProduct = Product::factory()->draft()->for($draftCategory)->create(['slug' => 'hidden-draft-dress']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('xml');

    $response->assertSee(route('home'))
        ->assertSee(url('/products'))
        ->assertSee(url('/products/textiles-aba'))
        ->assertSee(url('/products/textiles-aba/ankara-wax-fabric-bundle'))
        ->assertDontSee('hidden-draft-dress');
});

it('renders Organization JSON-LD and OpenGraph tags on the home page', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('<meta property="og:site_name"', false)
        ->assertSee('<meta property="og:type" content="website"', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image"', false)
        ->assertSee('"@type": "Organization"', false)
        ->assertSee(config('abatradehub.name'), false)
        ->assertSee(config('abatradehub.whatsapp_display'), false);
});

it('renders Product and BreadcrumbList JSON-LD and product OpenGraph tags on product detail page', function () {
    $category = Category::factory()->create(['name' => 'Footwear', 'slug' => 'footwear']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Aba Handcrafted Suede Loafers',
        'slug' => 'aba-handcrafted-suede-loafers',
        'sku' => 'ATH-FW-909',
        'base_price' => 17500,
    ]);

    $response = $this->get('/products/footwear/aba-handcrafted-suede-loafers');

    $response->assertOk()
        ->assertSee('<meta property="og:type" content="product"', false)
        ->assertSee('<meta property="product:retailer_item_id" content="ATH-FW-909"', false)
        ->assertSee('"@type": "Product"', false)
        ->assertSee('"sku": "ATH-FW-909"', false)
        ->assertSee('Aba Handcrafted Suede Loafers')
        ->assertSee('"@type": "BreadcrumbList"', false)
        ->assertSee('"name": "Footwear"', false);
});

