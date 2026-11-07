@props([
    'icon',
    'tone' => 'primary', // primary | secondary | tertiary
    'title',
    'footerIcon' => null,
    'footer' => null,
    'number' => null,
    'padding' => 'p-6',
])

@php
    $tones = [
        'primary' => ['badge' => 'bg-primary-fixed text-primary', 'text' => 'text-primary'],
        'secondary' => ['badge' => 'bg-secondary-fixed text-secondary', 'text' => 'text-secondary'],
        'tertiary' => ['badge' => 'bg-tertiary-fixed text-tertiary', 'text' => 'text-tertiary'],
    ];
    $t = $tones[$tone] ?? $tones['primary'];
@endphp

{{-- Used for both the "How it works" steps (with $number) and the "Why retailers trust us" benefits (with $icon). --}}
<div {{ $attributes->class(['flex flex-col rounded-xl bg-surface-container-low shadow-sm transition-shadow hover:shadow-md', $padding]) }}>
    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl text-headline-sm font-bold {{ $t['badge'] }}">
        @if ($number)
            {{ $number }}
        @else
            <x-ui.icon :name="$icon" class="text-[26px]" />
        @endif
    </div>
    <h3 class="mb-2 text-headline-sm font-semibold text-on-surface">{{ $title }}</h3>
    <p class="flex-1 text-body-md leading-relaxed text-on-surface-variant">{{ $slot }}</p>

    @isset($extra)
        {{ $extra }}
    @elseif ($footer)
        <div class="mt-6 flex items-center gap-2 pt-4 text-label-md {{ $t['text'] }}">
            @if ($footerIcon)
                <x-ui.icon :name="$footerIcon" class="text-[18px]" />
            @endif
            <span>{{ $footer }}</span>
        </div>
    @endisset
</div>

