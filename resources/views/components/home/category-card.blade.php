@props(['category'])

@php
    $url = url('/products/'.$category->slug);
    $price = \App\Support\Money::format($category->starting_price);
@endphp

<article class="group flex flex-col overflow-hidden rounded-2xl bg-surface-container-lowest shadow-sm transition-all duration-300 hover:shadow-xl">
    <a href="{{ $url }}" class="relative block aspect-[3/4] overflow-hidden bg-surface-container-high" tabindex="-1" aria-hidden="true">
        <x-product-image
            :product="$category->cover_product"
            conversion="large"
            :alt="$category->name"
            :fallback-icon="$category->icon ?: 'category'"
            class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
        />

        @if ($category->min_moq)
            <div class="absolute left-4 top-4">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-lowest px-3 py-1 text-label-sm text-on-surface shadow-sm">
                    <x-ui.icon name="check_circle" class="text-[14px] text-secondary" /> MOQ: {{ $category->min_moq }} pcs
                </span>
            </div>
        @endif

        @if ($price)
            <div class="absolute bottom-4 right-4">
                <span class="rounded-md bg-on-surface/80 px-2.5 py-1 text-label-sm text-surface backdrop-blur-sm">From {{ $price }} / pc</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col space-y-4 p-6">
        <div>
            <h3 class="text-headline-sm font-semibold text-on-surface">{{ $category->name }}</h3>
            @if ($category->description)
                <p class="mt-1 text-body-md text-on-surface-variant">{{ $category->description }}</p>
            @endif
            <p class="mt-2 text-label-md text-on-surface-variant">{{ $category->published_products_count }} {{ \Illuminate\Support\Str::plural('product', $category->published_products_count) }}</p>
        </div>

        <div class="mt-auto pt-2">
            <x-ui.button :href="$url" variant="surface" icon="arrow_forward" block>View {{ $category->name }}</x-ui.button>
        </div>
    </div>
</article>

