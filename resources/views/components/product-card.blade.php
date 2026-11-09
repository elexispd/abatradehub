@props([
    'product',
])

@php
    $startingPrice = \App\Support\Money::format($product->starting_price, $product->currency);
    $categoryName = $product->category?->name ?? 'Aba Sourced';
    $quickViewData = json_encode($product->toQuickViewArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<article
    {{ $attributes->class([
        'group flex flex-col justify-between rounded-xl bg-surface-container-lowest p-3 sm:p-4 shadow-sm transition-all duration-300 hover:shadow-md'
    ]) }}
>
    <div>
        {{-- Product Image with Overlay Tags --}}
        <div class="relative mb-3 sm:mb-4 aspect-[4/5] w-full overflow-hidden rounded-lg bg-surface-container-low">
            <a href="{{ $product->url }}" class="block h-full w-full" aria-label="View {{ $product->name }}">
                <x-product-image
                    :product="$product"
                    conversion="card"
                    :alt="$product->name"
                    class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                />
            </a>

            {{-- Top Badges --}}
            <div class="absolute left-2.5 top-2.5 flex flex-col items-start gap-1 sm:left-3 sm:top-3">
                <span class="rounded-full bg-surface-container-lowest/95 px-2 sm:px-2.5 py-0.5 sm:py-1 text-[10px] sm:text-label-sm font-semibold text-on-surface shadow-xs backdrop-blur-sm">
                    {{ $categoryName }}
                </span>
                @if ($product->is_featured)
                    <span class="rounded-full bg-secondary-container/95 px-2 sm:px-2.5 py-0.5 text-[10px] sm:text-label-sm font-bold text-on-secondary-container backdrop-blur-sm">
                        Featured
                    </span>
                @elseif ($product->moq <= 10)
                    <span class="rounded-full bg-secondary-container/95 px-2 sm:px-2.5 py-0.5 text-[10px] sm:text-label-sm font-bold text-on-secondary-container backdrop-blur-sm">
                        Low MOQ
                    </span>
                @endif
            </div>

            {{-- Quick View Button (hover desktop, always accessible) --}}
            <button
                type="button"
                x-on:click="$dispatch('open-quick-view', {{ $quickViewData }})"
                class="absolute bottom-2.5 right-2.5 flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-lg bg-surface/90 text-on-surface shadow-sm backdrop-blur-sm transition-transform hover:scale-105 hover:bg-surface active:scale-95"
                title="Quick preview"
                aria-label="Quick preview for {{ $product->name }}"
            >
                <x-ui.icon name="visibility" class="text-[18px] sm:text-[20px]" />
            </button>
        </div>

        {{-- Product Details --}}
        <div class="space-y-1 sm:space-y-2">
            <h3 class="text-body-md sm:text-headline-sm font-semibold text-on-surface transition-colors group-hover:text-primary line-clamp-2">
                <a href="{{ $product->url }}" class="focus-visible:outline-primary-container">
                    {{ $product->name }}
                </a>
            </h3>

            @if ($product->short_description)
                <p class="hidden sm:block text-body-sm text-on-surface-variant line-clamp-2">
                    {{ $product->short_description }}
                </p>
            @endif
        </div>

        {{-- Price Box --}}
        <div class="mt-2.5 sm:mt-3 rounded-lg bg-surface-container-low p-2 sm:p-3 space-y-0.5">
            <div class="flex items-baseline justify-between gap-1">
                <span class="text-[11px] sm:text-label-md text-on-surface-variant">Wholesale:</span>
                <span class="text-label-lg sm:text-headline-md font-bold text-primary">
                    {{ $startingPrice }}
                    <span class="text-[10px] sm:text-body-sm font-normal text-on-surface-variant">/ pc</span>
                </span>
            </div>
            @if ($product->priceTiers->isNotEmpty())
                <div class="flex items-center justify-between text-[11px] sm:text-body-sm text-on-surface-variant pt-0.5">
                    <span>Tiers:</span>
                    <span class="font-semibold text-secondary">
                        From {{ \App\Support\Money::format($product->priceTiers->min('unit_price'), $product->currency) }}
                    </span>
                </div>
            @endif
        </div>

        {{-- MOQ & Lead Time --}}
        <div class="mt-2 flex flex-wrap items-center justify-between gap-1.5 pt-1">
            <span class="inline-flex items-center gap-1 rounded-full bg-surface-container-high px-2 sm:px-2.5 py-0.5 sm:py-1 text-[11px] sm:text-label-sm text-on-surface">
                <x-ui.icon name="inventory" class="text-[13px] sm:text-[15px] text-primary" />
                <span>MOQ: {{ $product->moq }} {{ $product->moq_unit }}</span>
            </span>

            @if ($product->lead_time_label)
                <span class="text-[11px] sm:text-label-sm text-on-surface-variant">
                    {{ $product->lead_time_label }}
                </span>
            @endif
        </div>
    </div>

    {{-- Actions: Enquiry & Add to Basket buttons --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 sm:gap-2 pt-2.5 sm:pt-3">
        <button
            type="button"
            x-on:click="$dispatch('open-enquiry-modal', { product: {{ $quickViewData }} })"
            class="flex h-8 sm:h-10 items-center justify-center gap-1 sm:gap-1.5 rounded-lg bg-primary-container px-2 sm:px-3 text-center text-label-sm sm:text-label-md font-bold text-on-primary-container shadow-xs transition-colors hover:bg-primary active:scale-[0.99]"
            title="Make a wholesale enquiry"
        >
            <x-ui.icon name="edit_note" class="text-[16px] sm:text-[18px]" />
            <span>Enquire</span>
        </button>

        <button
            type="button"
            x-on:click="$dispatch('add-to-basket', { product: {{ $quickViewData }} })"
            class="flex h-8 sm:h-10 items-center justify-center gap-1 sm:gap-1.5 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high hover:text-secondary px-2 sm:px-3 text-center text-label-sm sm:text-label-md font-bold transition-colors active:scale-[0.99] border border-outline-variant/30"
            title="Add {{ $product->name }} to Wholesale Basket"
        >
            <x-ui.icon name="shopping_basket" class="text-[16px] sm:text-[18px] text-secondary" />
            <span>+ Basket</span>
        </button>
    </div>
</article>

