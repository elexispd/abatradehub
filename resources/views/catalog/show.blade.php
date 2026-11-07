@php
    $startingPrice = \App\Support\Money::format($product->starting_price, $product->currency);
    $primaryMedia = $product->getFirstMedia('images');
    $allMedia = $product->getMedia('images');
    $firstImageUrl = $primaryMedia ? ($primaryMedia->hasGeneratedConversion('large') ? $primaryMedia->getUrl('large') : $primaryMedia->getUrl()) : '';

    $quickViewData = json_encode($product->toQuickViewArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $crumbs = [
        ['label' => 'Shop / Catalogue', 'url' => url('/products')],
        ['label' => $category->name, 'url' => url('/products/' . $category->slug)],
        ['label' => $product->name],
    ];
@endphp

<x-layouts.public
    :title="$product->meta_title ?: $product->name . ' - Wholesale Sourcing'"
    :description="$product->meta_description ?: ($product->short_description ?: 'Wholesale sourcing for ' . $product->name . ' in Aba. MOQ: ' . $product->moq . ' ' . $product->moq_unit . '.')"
    type="product"
    :product="$product"
>
    <x-slot:head>
        <x-seo.product-schema :product="$product" />
        <x-seo.breadcrumb-schema :items="$crumbs" />
    </x-slot:head>

    <div class="w-full">
        {{-- Breadcrumbs --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2">
            <x-ui.breadcrumbs :items="$crumbs" />
        </div>

        {{-- Main Product Detail Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
            <div
                class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start"
                x-data="{
                    activeImage: '{{ $firstImageUrl }}',
                    init() {
                        if (!this.activeImage && {{ count($allMedia) }} > 0) {
                            this.activeImage = '{{ $firstImageUrl }}';
                        }
                    }
                }"
            >
                {{-- Left: Image Gallery (5 cols) --}}
                <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-28">
                    <div class="aspect-[4/5] w-full overflow-hidden rounded-2xl bg-surface-container-low shadow-md relative">
                        @if ($primaryMedia)
                            <img
                                x-bind:src="activeImage"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover object-center transition-all duration-300"
                            />
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-surface-container-high text-outline">
                                <x-ui.icon name="checkroom" class="text-[64px]" />
                            </div>
                        @endif

                        {{-- Floating Badges --}}
                        <div class="absolute left-3 top-3 flex flex-col gap-1.5">
                            <span class="rounded-full bg-surface-container-lowest/95 px-3 py-1 text-label-sm font-semibold text-on-surface shadow-xs backdrop-blur-sm">
                                {{ $category->name }}
                            </span>
                            @if ($product->is_featured)
                                <span class="rounded-full bg-secondary-container/95 px-3 py-1 text-label-sm font-bold text-on-secondary-container backdrop-blur-sm">
                                    Featured Style
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Image Thumbnails (if multiple images) --}}
                    @if ($allMedia->count() > 1)
                        <div class="flex items-center gap-3 overflow-x-auto pb-1 pt-1">
                            @foreach ($allMedia as $media)
                                @php
                                    $thumbUrl = $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl();
                                    $largeUrl = $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl();
                                @endphp
                                <button
                                    type="button"
                                    x-on:click="activeImage = '{{ $largeUrl }}'"
                                    class="h-16 w-16 sm:h-20 sm:w-20 shrink-0 rounded-xl overflow-hidden border-2 transition-all"
                                    x-bind:class="activeImage === '{{ $largeUrl }}' ? 'border-primary ring-2 ring-primary/20' : 'border-transparent opacity-75 hover:opacity-100'"
                                    aria-label="View photo {{ $loop->iteration }}"
                                >
                                    <img src="{{ $thumbUrl }}" alt="Thumbnail {{ $loop->iteration }}" class="h-full w-full object-cover" loading="lazy" />
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Trust Micro-badge --}}
                    <div class="rounded-xl bg-surface-container-low p-3.5 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-secondary-fixed text-secondary flex items-center justify-center shrink-0">
                            <x-ui.icon name="verified" class="text-[20px]" />
                        </div>
                        <div>
                            <p class="text-label-md font-semibold text-on-surface">Inspected before dispatch</p>
                            <p class="text-body-sm text-on-surface-variant">Seams, zippers, and sizing verified in person in Aba.</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Product Details & Wholesale Terms (7 cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 text-label-sm text-secondary font-semibold">
                            <x-ui.icon name="store" class="text-[16px]" />
                            <span>{{ $product->supplier?->market_location ?: 'Ariaria Production Cluster, Aba, Abia State' }}</span>
                            <span>&bull;</span>
                            <span class="text-on-surface-variant">Verified Aba Manufacturer</span>
                        </div>

                        <h1 class="text-display-lg-mobile sm:text-headline-lg font-bold text-on-surface tracking-tight mt-1.5">
                            {{ $product->name }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-3 mt-2 text-label-md text-on-surface-variant">
                            <span class="inline-flex items-center gap-1 rounded bg-surface-container px-2 py-0.5 text-on-surface font-semibold">
                                SKU: {{ $product->sku }}
                            </span>
                            @if ($product->lead_time_label)
                                <span class="inline-flex items-center gap-1 text-on-surface">
                                    <x-ui.icon name="schedule" class="text-[16px] text-tertiary" />
                                    <span>Lead Time: {{ $product->lead_time_label }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Pricing Box --}}
                    <div class="rounded-2xl bg-surface-container-low p-5 sm:p-6 space-y-4">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <div>
                                <span class="text-label-md uppercase tracking-wider text-on-surface-variant font-bold block">
                                    Wholesale Rate
                                </span>
                                <span class="text-display-lg-mobile sm:text-display-lg font-bold text-primary">
                                    {{ $startingPrice }}
                                    <span class="text-body-md font-normal text-on-surface-variant">/ piece</span>
                                </span>
                            </div>

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary-fixed px-3 py-1 text-label-sm font-bold text-on-secondary-fixed-variant">
                                <x-ui.icon name="check_circle" class="text-[16px]" />
                                MOQ: {{ $product->moq }} {{ $product->moq_unit }}
                            </span>
                        </div>

                        {{-- Tiered Pricing Table (if price tiers exist) --}}
                        @if ($product->priceTiers->isNotEmpty())
                            <div class="pt-3 border-t border-outline-variant/30 space-y-2">
                                <span class="text-label-sm font-bold uppercase tracking-wider text-on-surface-variant block">
                                    Wholesale Volume Discount Tiers
                                </span>
                                <div class="overflow-hidden rounded-xl bg-surface-container-lowest border border-outline-variant/30">
                                    <table class="w-full text-left text-body-sm">
                                        <thead class="bg-surface-container text-label-sm font-semibold text-on-surface">
                                            <tr>
                                                <th class="px-4 py-2.5">Order Quantity</th>
                                                <th class="px-4 py-2.5">Price Per Unit</th>
                                                <th class="px-4 py-2.5 text-right">Saving</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-outline-variant/20">
                                            @foreach ($product->priceTiers as $tier)
                                                @php
                                                    $tierPriceFormatted = \App\Support\Money::format($tier->unit_price, $product->currency);
                                                    $savingPercent = $product->base_price && $product->base_price > $tier->unit_price
                                                        ? round((($product->base_price - $tier->unit_price) / $product->base_price) * 100)
                                                        : null;
                                                @endphp
                                                <tr class="hover:bg-surface-container-low transition-colors">
                                                    <td class="px-4 py-3 font-semibold text-on-surface">
                                                        {{ $tier->range_label }} {{ $product->moq_unit }}
                                                    </td>
                                                    <td class="px-4 py-3 font-bold text-primary">
                                                        {{ $tierPriceFormatted }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        @if ($savingPercent)
                                                            <span class="rounded bg-secondary-container px-2 py-0.5 text-xs font-bold text-on-secondary-container">
                                                                {{ $savingPercent }}% OFF
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-on-surface-variant">Base tier</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- High-Priority Action Buttons --}}
                    <div class="space-y-3 pt-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a
                                href="{{ $product->whatsapp_url }}"
                                target="_blank"
                                rel="noopener"
                                class="flex min-h-12 items-center justify-center gap-2.5 rounded-xl bg-secondary px-6 py-3.5 text-label-lg font-bold text-on-secondary shadow-md hover:bg-secondary/90 transition-all active:scale-[0.99]"
                            >
                                <x-ui.icon name="chat" class="text-[22px]" />
                                <span>Order on WhatsApp</span>
                            </a>

                            <button
                                type="button"
                                x-on:click="$dispatch('open-enquiry-modal', { product: {{ $quickViewData }} })"
                                class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary-container px-6 py-3.5 text-label-lg font-bold text-on-primary-container shadow-md hover:bg-primary transition-all active:scale-[0.99]"
                            >
                                <x-ui.icon name="edit_note" class="text-[22px]" />
                                <span>Submit Bulk Enquiry</span>
                            </button>
                        </div>
                        <p class="text-center text-body-sm text-on-surface-variant">
                            WhatsApp orders connect directly with our sourcing desk for immediate photo &amp; size confirmation.
                        </p>
                    </div>

                    {{-- Specifications & Terms Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="rounded-xl bg-surface-container-low p-4 space-y-1">
                            <span class="text-label-sm font-semibold text-on-surface-variant block">Minimum Order Quantity</span>
                            <p class="text-headline-sm font-bold text-on-surface">{{ $product->moq }} {{ $product->moq_unit }}</p>
                            <p class="text-body-sm text-on-surface-variant">Mix sizes S to XXL freely</p>
                        </div>

                        <div class="rounded-xl bg-surface-container-low p-4 space-y-1">
                            <span class="text-label-sm font-semibold text-on-surface-variant block">Production &amp; Dispatch</span>
                            <p class="text-headline-sm font-bold text-on-surface">{{ $product->lead_time_label ?: '3-5 Days' }}</p>
                            <p class="text-body-sm text-on-surface-variant">Fast turn-around from Aba factories</p>
                        </div>

                        <div class="rounded-xl bg-surface-container-low p-4 space-y-1">
                            <span class="text-label-sm font-semibold text-on-surface-variant block">Nationwide Delivery</span>
                            <p class="text-body-md font-semibold text-on-surface">All Interstate Commercial Parks</p>
                            <p class="text-body-sm text-on-surface-variant">GIG, Peace, God Is Good &amp; air cargo</p>
                        </div>

                        <div class="rounded-xl bg-surface-container-low p-4 space-y-1">
                            <span class="text-label-sm font-semibold text-on-surface-variant block">Pre-Shipment Inspection</span>
                            <p class="text-body-md font-semibold text-secondary">100% Quality Checked</p>
                            <p class="text-body-sm text-on-surface-variant">Video confirmation before sealing packages</p>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if ($product->description || $product->short_description)
                        <div class="pt-4 border-t border-outline-variant/30 space-y-3">
                            <h2 class="text-headline-sm font-bold text-on-surface">Product &amp; Material Description</h2>
                            @if ($product->short_description)
                                <p class="text-body-md text-on-surface font-medium leading-relaxed">
                                    {{ $product->short_description }}
                                </p>
                            @endif
                            @if ($product->description)
                                <div class="prose max-w-none text-body-md text-on-surface-variant leading-relaxed">
                                    {!! $product->description !!}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Related Products Section --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full border-t border-outline-variant/30">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-label-sm font-bold uppercase tracking-widest text-primary">More from this collection</span>
                        <h2 class="text-headline-lg font-bold text-on-surface mt-1">Related Wholesale Styles</h2>
                    </div>
                    <a href="{{ url('/products/' . $category->slug) }}" class="inline-flex items-center gap-1.5 text-label-lg font-semibold text-primary hover:underline">
                        <span>View all {{ $category->name }}</span>
                        <x-ui.icon name="arrow_forward" class="text-[18px]" />
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach ($relatedProducts as $relProduct)
                        <x-product-card :product="$relProduct" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Wholesale FAQ Section --}}
        <x-catalog.faq />

        {{-- Fast Enquiry Modal (Livewire) --}}
        <livewire:catalog-enquiry-modal />

        {{-- Quick View Modal (Alpine) --}}
        <x-catalog.quick-view-modal />
    </div>
</x-layouts.public>

