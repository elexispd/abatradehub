<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $published = fn ($query) => $query->published();

        $categories = Category::query()
            ->active()
            ->whereHas('products', $published)
            ->withCount(['products as published_products_count' => $published])
            ->withMin(['products as min_base_price' => $published], 'base_price')
            ->withMin(['products as min_moq' => $published], 'moq')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Lowest tier price per category (products that only have tiers, no base price).
        $tierMins = PriceTier::query()
            ->join('products', 'products.id', '=', 'price_tiers.product_id')
            ->whereIn('products.category_id', $categories->pluck('id'))
            ->where('products.status', 'published')
            ->whereNull('products.deleted_at')
            ->groupBy('products.category_id')
            ->selectRaw('products.category_id, MIN(price_tiers.unit_price) as min_price')
            ->pluck('min_price', 'category_id');

        // A representative product (and so a cover image) for each category.
        $covers = Product::published()
            ->with('media')
            ->whereIn('category_id', $categories->pluck('id'))
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($products) => $products->first(fn (Product $p) => $p->hasMedia('images')) ?? $products->first());

        $categories->each(function (Category $category) use ($tierMins, $covers) {
            $category->starting_price = collect([$category->min_base_price, $tierMins[$category->id] ?? null])
                ->filter(fn ($p) => $p !== null)
                ->min();
            $category->cover_product = $covers[$category->id] ?? null;
        });

        $heroProducts = Product::published()
            ->with('media')
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->limit(12)
            ->get()
            ->filter(fn (Product $p) => $p->hasMedia('images'))
            ->take(4)
            ->values();

        return view('home', [
            'categories' => $categories,
            'heroProducts' => $heroProducts,
            'minMoq' => Product::published()->min('moq'),
            'productCount' => Product::published()->count(),
            'supplierCount' => Product::published()->whereNotNull('supplier_id')->distinct()->count('supplier_id'),
        ]);
    }
}

