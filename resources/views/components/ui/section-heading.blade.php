@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'tone' => 'primary', // primary | secondary | tertiary
    'align' => 'left',   // left | center
    'tag' => 'h2',
])

@php
    $tones = ['primary' => 'text-primary', 'secondary' => 'text-secondary', 'tertiary' => 'text-tertiary'];
@endphp

<div {{ $attributes->class(['space-y-3', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <span class="block text-label-sm font-bold uppercase tracking-widest {{ $tones[$tone] ?? $tones['primary'] }}">{{ $eyebrow }}</span>
    @endif
    <{{ $tag }} class="text-headline-lg font-bold text-on-surface">{{ $title }}</{{ $tag }}>
    @if ($subtitle)
        <p class="text-body-lg text-on-surface-variant">{{ $subtitle }}</p>
    @endif
</div>

