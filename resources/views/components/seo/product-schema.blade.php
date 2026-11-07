@props([
    'product',
])

@php
    $images = $product->getMedia('images')->map(function ($media) {
        return $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl();
    })->values()->all();

    $cleanDescription = strip_tags($product->short_description ?: $product->description ?: $product->name);

    if ($product->priceTiers->isNotEmpty()) {
        $offers = [
            '@type' => 'AggregateOffer',
            'priceCurrency' => $product->currency ?: 'NGN',
            'lowPrice' => (float) $product->priceTiers->min('unit_price'),
            'highPrice' => (float) ($product->base_price ?: $product->priceTiers->max('unit_price')),
            'offerCount' => $product->priceTiers->count(),
            'availability' => 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            'url' => $product->url,
            'seller' => [
                '@type' => 'Organization',
                'name' => config('abatradehub.name', 'AbaTradeHub'),
            ],
        ];
    } else {
        $offers = [
            '@type' => 'Offer',
            'priceCurrency' => $product->currency ?: 'NGN',
            'price' => (float) ($product->starting_price ?: $product->base_price ?: 0),
            'availability' => 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            'url' => $product->url,
            'seller' => [
                '@type' => 'Organization',
                'name' => config('abatradehub.name', 'AbaTradeHub'),
            ],
        ];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $images,
        'description' => $cleanDescription,
        'sku' => $product->sku,
        'category' => $product->category?->name,
        'brand' => [
            '@type' => 'Brand',
            'name' => 'Aba Made',
        ],
        'offers' => $offers,
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

