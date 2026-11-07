@props([
    'variant' => 'primary', // primary | secondary | whatsapp | surface | outline
    'size' => 'md',         // sm | md | lg
    'href' => null,
    'icon' => null,
    'iconPosition' => 'trailing',
    'block' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary-container text-on-primary-container hover:bg-primary shadow-sm',
        'secondary' => 'bg-surface-container text-on-surface hover:bg-surface-container-high shadow-sm',
        'surface' => 'bg-surface-container text-on-surface hover:bg-primary-container hover:text-on-primary-container shadow-sm',
        'whatsapp' => 'bg-secondary text-on-secondary hover:bg-secondary/90 shadow-sm',
        'outline' => 'border border-outline-variant bg-transparent text-on-surface hover:bg-surface-container',
    ];
    $sizes = [
        'sm' => 'min-h-11 px-4 py-2 text-label-md',
        'md' => 'min-h-11 px-5 py-3 text-label-lg',
        'lg' => 'min-h-12 px-6 py-3.5 text-label-lg',
    ];
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-container '
        .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']).($block ? ' w-full' : '');
    $iconSize = $size === 'sm' ? 'text-[16px]' : 'text-[20px]';
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->except('type')->merge(['class' => $classes]) }}
>
    @if ($icon && $iconPosition === 'leading')
        <x-ui.icon :name="$icon" :class="$iconSize" />
    @endif
    <span>{{ $slot }}</span>
    @if ($icon && $iconPosition === 'trailing')
        <x-ui.icon :name="$icon" :class="$iconSize" />
    @endif
</{{ $tag }}>

