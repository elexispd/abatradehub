<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// SEO: Sitemap & Robots
Route::get('/sitemap.xml', SitemapController::class)->name('seo.sitemap');
Route::get('/robots.txt', function () {
    $sitemap = url('/sitemap.xml');
    $content = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nDisallow: /profile\n\nSitemap: {$sitemap}\n";

    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('seo.robots');

// Honeypot / Scanner Decoy: Block generic /admin probe attempts
Route::any('/admin{any}', function () {
    \Illuminate\Support\Facades\Log::notice('Blocked scanner probe on generic /admin path from IP: ' . request()->ip());
    abort(404);
})->where('any', '.*');

// Customer Wholesale Order & Shipment Tracking
Route::get('/track-order', function () {
    return view('orders.track');
})->name('orders.track');
Route::get('/track', fn () => redirect()->route('orders.track'));
Route::get('/orders/{sale}/track', function (\Illuminate\Http\Request $request, \App\Models\Sale $sale) {
    $reference = $request->query('reference');
    $refValid = $reference && $sale->payments()->where('reference', $reference)->exists();
    $signedValid = $request->hasValidSignature();
    $staffValid = auth()->check() && (auth()->user()->can('view', $sale) || auth()->user()->isSuperAdmin());

    if ($refValid || $signedValid || $staffValid) {
        return redirect()->route('orders.track', ['invoice' => $sale->invoice_number]);
    }

    // Do not leak invoice numbers of arbitrary integer sale IDs to unauthenticated visitors
    return redirect()->route('orders.track');
})->name('orders.track.sale');

// Wholesale Catalog & Category Routes with Clean URLs
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{category:slug}', [ProductController::class, 'index'])->name('products.category');
Route::get('/products/{category:slug}/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show')
    ->withoutScopedBindings();

// Paystack Online Wholesale Checkout Routes
Route::get('/payment/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/payment/success/{sale}', [\App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
Route::get('/orders/{sale}/receipt', [\App\Http\Controllers\PaymentController::class, 'receipt'])->name('payment.receipt');
Route::post('/webhook/paystack', [\App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin Invoice Print View
Route::get('/' . \App\Support\Admin::path('sales/{sale}/invoice'), [\App\Http\Controllers\InvoiceController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('filament.admin.sales.invoice');

require __DIR__.'/auth.php';
