<?php

use App\Livewire\QuoteForm;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use Livewire\Livewire;

it('renders the home page with database-driven categories', function () {
    $category = Category::factory()->create(['name' => 'Test Footwear', 'slug' => 'test-footwear']);
    Product::factory()->for($category)->create(['moq' => 12, 'base_price' => 4500]);
    $draftCategory = Category::factory()->create(['name' => 'Hidden Draft Category']);
    Product::factory()->draft()->for($draftCategory)->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('How It Works')
        ->assertSee('Test Footwear')
        ->assertSee('MOQ: 12 pcs')
        ->assertSee('From ₦4,500 / pc')
        ->assertSee(config('abatradehub.whatsapp_number'))
        ->assertDontSee('Hidden Draft Category');
});

it('saves a quote request as a new enquiry', function () {
    Livewire::test(QuoteForm::class)
        ->assertSet('country', 'Nigeria')
        ->set('name', 'Amaka')
        ->set('phone', '+234 801 234 5678')
        ->set('message', '20 pairs of sandals')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    expect(Enquiry::first())
        ->name->toBe('Amaka')
        ->country->toBe('Nigeria')
        ->status->value->toBe('new');
});

it('validates the quote form', function () {
    Livewire::test(QuoteForm::class)
        ->call('submit')
        ->assertHasErrors(['name', 'phone', 'message']);
});

it('only lets admins into the admin panel', function () {
    $this->get('/admin')->assertRedirect();

    $this->actingAs(\App\Models\User::factory()->create())->get('/admin')->assertForbidden();
    $this->actingAs(\App\Models\User::factory()->admin()->create())->get('/admin')->assertOk();
});
