@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('abatradehub.name', 'AbaTradeHub'),
        'legalName' => config('abatradehub.name', 'AbaTradeHub') . ' Wholesale Sourcing',
        'url' => url('/'),
        'logo' => url('/favicon.ico'),
        'description' => config('abatradehub.description'),
        'telephone' => config('abatradehub.whatsapp_display'),
        'email' => config('abatradehub.email'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Aba Garment Cluster, Ariaria Production Hub',
            'addressLocality' => 'Aba',
            'addressRegion' => 'Abia State',
            'addressCountry' => 'NG',
        ],
        'contactPoint' => [
            [
                '@type' => 'ContactPoint',
                'telephone' => config('abatradehub.whatsapp_display'),
                'contactType' => 'sales',
                'contactOption' => 'TollFree',
                'areaServed' => ['NG', 'GH', 'KE', 'CM', 'SN', 'CI'],
                'availableLanguage' => ['English', 'Igbo'],
            ],
        ],
        'knowsAbout' => [
            'Wholesale Clothing Manufacturing',
            'Wholesale Fashion Footwear Manufacturing',
            'Wholesale Bag Manufacturing',
            'Aba Garment Production',
            'Bulk Senator Wear',
            'Native Wears',
            'African Fashion Wholesale',
            'Textiles and Leather Footwear',
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

