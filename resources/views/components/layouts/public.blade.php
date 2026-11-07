@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'type' => 'website',
    'image' => null,
    'product' => null,
])

@php
    $siteName = config('abatradehub.name', 'AbaTradeHub');
    $tagline = config('abatradehub.tagline', 'Wholesale Sourcing');
    $pageTitle = $title ? "{$title} | {$siteName}" : "{$siteName} | {$tagline}";
    $pageDescription = $description ?? config('abatradehub.description');
    $canonicalUrl = $canonical ?? url()->current();

    // Determine social share image
    $ogImage = $image;
    if (!$ogImage && $product) {
        $primaryMedia = $product->getFirstMedia('images');
        $ogImage = $primaryMedia ? ($primaryMedia->hasGeneratedConversion('card') ? $primaryMedia->getUrl('card') : $primaryMedia->getUrl()) : null;
    }
    $ogImage = $ogImage ?: asset('img/logo.png');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/favicon.png') }}">

    {{-- Open Graph / Facebook / WhatsApp --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="en_NG">

    {{-- Product Specific Open Graph Meta --}}
    @if ($product && $type === 'product')
        @if ($product->starting_price)
            <meta property="product:price:amount" content="{{ $product->starting_price }}">
            <meta property="product:price:currency" content="{{ $product->currency ?: 'NGN' }}">
        @endif
        <meta property="product:retailer_item_id" content="{{ $product->sku }}">
        <meta property="product:availability" content="in stock">
        <meta property="product:condition" content="new">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Fonts: Plus Jakarta Sans (text) + Material Symbols (icons) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0..1,0&display=block">

    {{-- Page-specific Head slot / JSON-LD Schemas --}}
    {{ $head ?? '' }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-background text-on-surface antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:text-on-primary">
        Skip to content
    </a>

    <x-site.header />

    <main id="main" class="w-full bg-background pt-24 lg:pt-28">
        {{ $slot }}
    </main>

    <x-site.footer />

    {{-- Floating WhatsApp Quick Contact Button --}}
    <aside class="fixed bottom-5 right-5 z-40">
        <a
            href="{{ \App\Support\Whatsapp::url('Hello ' . config('abatradehub.name') . ', I would like to make an enquiry about wholesale sourcing.') }}"
            target="_blank"
            rel="noopener"
            class="flex min-h-12 items-center gap-2 rounded-full bg-secondary px-4 py-3 text-label-md font-bold text-on-secondary shadow-lg transition-transform hover:scale-105 hover:bg-secondary/90 active:scale-95"
            aria-label="Chat on WhatsApp with {{ config('abatradehub.name') }}"
        >
            <x-ui.icon name="chat" class="text-[22px]" />
            <span class="hidden sm:inline">WhatsApp Us</span>
        </a>
    </aside>

    @livewireScripts
</body>
</html>
