<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(?Category $category = null): View
    {
        $publishedScope = fn ($query) => $query->published();

        $categories = Category::query()
            ->active()
            ->whereHas('products', $publishedScope)
            ->withCount(['products as published_products_count' => $publishedScope])
            ->orderBy('sort_order')
            ->get();

        $totalProductCount = Product::published()->count();

        $metaTitle = $category
            ? "Wholesale {$category->name} - Direct Aba Sourcing"
            : 'Wholesale Catalog & Bulk Sourcing';

        $metaDescription = $category
            ? ($category->description ?: "Source wholesale {$category->name} directly from verified Aba garment manufacturers and traders. Low MOQs, inspected before dispatch.")
            : 'Browse verified Aba garment styles, fabrics, and footwear ready for bulk wholesale production. Low starter minimums (from 10 pcs) with nationwide and regional delivery.';

        return view('catalog.index', [
            'selectedCategory' => $category,
            'categories' => $categories,
            'totalProductCount' => $totalProductCount,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
        ]);
    }

    public function show(Category $category, Product $product): View|RedirectResponse
    {
        // Ensure canonical category URL
        if ($product->category_id !== $category->id) {
            return redirect()->to($product->url, 301);
        }

        // Only allow published products to be viewed publicly
        if ($product->status !== ProductStatus::Published || $product->trashed()) {
            abort(404);
        }

        $product->load(['category', 'media', 'priceTiers', 'supplier']);

        $relatedProducts = Product::published()
            ->with(['category', 'media', 'priceTiers'])
            ->where('category_id', $category->id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('catalog.show', [
            'category' => $category,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}

