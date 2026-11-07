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

// Wholesale Catalog & Category Routes with Clean URLs
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{category:slug}', [ProductController::class, 'index'])->name('products.category');
Route::get('/products/{category:slug}/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show')
    ->withoutScopedBindings();

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
