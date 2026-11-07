<?php

use App\Livewire\CatalogEnquiryModal;
use App\Livewire\QuoteForm;
use App\Mail\NewEnquiryNotification;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('generates a pre-filled WhatsApp link with product name, SKU, and page URL on every product', function () {
    $category = Category::factory()->create(['name' => 'Men Senator', 'slug' => 'men-senator']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Royal Blue Embroidered Senator',
        'slug' => 'royal-blue-embroidered-senator',
        'sku' => 'ATH-MS-2026',
    ]);

    $waUrl = $product->whatsapp_url;

    expect($waUrl)
        ->toContain('https://wa.me/' . config('abatradehub.whatsapp_number'))
        ->toContain(rawurlencode('Royal Blue Embroidered Senator'))
        ->toContain(rawurlencode('ATH-MS-2026'))
        ->toContain(rawurlencode(url('/products/men-senator/royal-blue-embroidered-senator')));
});

it('sends an admin email notification when the home quote form is submitted', function () {
    Mail::fake();

    Livewire::test(QuoteForm::class)
        ->set('name', 'Ngozi Boutique')
        ->set('phone', '+234 803 999 8888')
        ->set('message', 'Need 50 pieces of ladies linen dresses.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $enquiry = Enquiry::where('name', 'Ngozi Boutique')->first();
    expect($enquiry)->not->toBeNull();

    $adminEmail = config('abatradehub.enquiry_notification_email');

    Mail::assertSent(NewEnquiryNotification::class, function ($mail) use ($adminEmail, $enquiry) {
        return $mail->hasTo($adminEmail) &&
               $mail->enquiry->id === $enquiry->id;
    });
});

it('sends an admin email notification with product details when the catalog enquiry modal is submitted', function () {
    Mail::fake();

    $category = Category::factory()->create(['slug' => 'footwear']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Handmade Leather Loafers',
        'slug' => 'handmade-leather-loafers',
        'sku' => 'ATH-FW-501',
        'moq' => 12,
    ]);

    Livewire::test(CatalogEnquiryModal::class)
        ->dispatch('open-enquiry-modal', $product->toQuickViewArray())
        ->set('name', 'Emeka Footwear Store')
        ->set('phone', '+234 812 345 6789')
        ->set('quantity', 24)
        ->set('destination', 'Ariaria Market, Aba')
        ->set('notes', '12 Brown, 12 Black, sizes 41-45')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $enquiry = Enquiry::where('name', 'Emeka Footwear Store')->first();
    expect($enquiry)->not->toBeNull();
    expect($enquiry->items)->toHaveCount(1);
    expect($enquiry->items->first()->product_sku)->toBe('ATH-FW-501');

    $adminEmail = config('abatradehub.enquiry_notification_email');

    Mail::assertSent(NewEnquiryNotification::class, function ($mail) use ($adminEmail, $enquiry) {
        return $mail->hasTo($adminEmail) &&
               $mail->enquiry->items->first()->product_sku === 'ATH-FW-501';
    });
});

it('renders the email notification template with reference and quick action links', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create([
        'name' => 'Senator Suit Navy',
        'sku' => 'ATH-SS-11',
    ]);

    $enquiry = Enquiry::create([
        'name' => 'Chioma Stores',
        'phone' => '+2348031112233',
        'email' => 'chioma@example.com',
        'country' => 'Benin Republic',
        'city' => 'Cotonou Market',
        'message' => 'Looking for 30 sets immediately.',
        'source' => 'form',
    ]);

    $enquiry->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'quantity' => 30,
        'note' => 'Mix L and XL',
    ]);

    $mailable = new NewEnquiryNotification($enquiry);
    $mailable->assertSeeInHtml($enquiry->reference);
    $mailable->assertSeeInHtml('Chioma Stores');
    $mailable->assertSeeInHtml('Benin Republic');
    $mailable->assertSeeInHtml('Senator Suit Navy');
    $mailable->assertSeeInHtml('30 pcs');
    $mailable->assertSeeInHtml('Cotonou Market');
    $mailable->assertSeeInHtml('https://wa.me/2348031112233');
    $mailable->assertSeeInHtml(url("/admin/enquiries/{$enquiry->id}/edit"));
});

