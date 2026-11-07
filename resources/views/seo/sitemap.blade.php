{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    {{-- Home Page --}}
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ $latestUpdate->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Wholesale Catalog --}}
    <url>
        <loc>{{ url('/products') }}</loc>
        <lastmod>{{ $latestUpdate->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Category Pages --}}
    @foreach ($categories as $category)
        <url>
            <loc>{{ url('/products/' . $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    {{-- Product Detail Pages --}}
    @foreach ($products as $product)
        <url>
            <loc>{{ $product->url }}</loc>
            <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
            @php $primaryImage = $product->getFirstMedia('images'); @endphp
            @if ($primaryImage)
                <image:image>
                    <image:loc>{{ $primaryImage->hasGeneratedConversion('large') ? $primaryImage->getUrl('large') : $primaryImage->getUrl() }}</image:loc>
                    <image:title>{{ $product->name }}</image:title>
                    <image:caption>{{ $product->short_description ?: $product->name }}</image:caption>
                </image:image>
            @endif
        </url>
    @endforeach
</urlset>

