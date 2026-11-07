<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $publishedScope = fn ($query) => $query->published();

        $categories = Category::query()
            ->active()
            ->whereHas('products', $publishedScope)
            ->latest('updated_at')
            ->get();

        $products = Product::published()
            ->with(['category', 'media'])
            ->latest('updated_at')
            ->get();

        $latestUpdate = $products->first()?->updated_at ?? now();

        $xml = view('seo.sitemap', [
            'categories' => $categories,
            'products' => $products,
            'latestUpdate' => $latestUpdate,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

