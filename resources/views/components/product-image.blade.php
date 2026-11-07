@props([
    'product' => null,
    'conversion' => 'card', // thumb | card | large
    'alt' => null,
    'lazy' => true,
    'fallbackIcon' => 'checkroom',
])

@php
    $media = $product?->getFirstMedia('images');
    // thumb and card are square crops; large keeps the original aspect ratio.
    $square = ['thumb' => 300, 'card' => 600][$conversion] ?? null;
@endphp

@if ($media)
    <img
        src="{{ $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl() }}"
        alt="{{ $alt ?? $product->name }}"
        @if ($square) width="{{ $square }}" height="{{ $square }}" @endif
        loading="{{ $lazy ? 'lazy' : 'eager' }}"
        decoding="async"
        @unless ($lazy) fetchpriority="high" @endunless
        {{ $attributes }}
    >
@else
    <div {{ $attributes->class(['flex items-center justify-center bg-surface-container-high text-outline']) }} role="img" aria-label="{{ $alt ?? $product?->name ?? 'No image' }}">
        <x-ui.icon :name="$fallbackIcon" class="text-[48px]" />
    </div>
@endif
