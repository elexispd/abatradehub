<div>
    {{-- Top Callout Banner (faithful to catalog.html) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-2 w-full">
        <div class="bg-surface-container-low rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container text-primary text-label-sm uppercase tracking-wider font-bold">
                        <x-ui.icon name="warehouse" class="text-[16px]" />
                        <span>Direct Aba Production Hub</span>
                    </div>

                    <h1 class="text-headline-lg sm:text-display-lg font-bold text-on-surface tracking-tight">
                        Wholesale Catalog &amp; Bulk Sourcing
                    </h1>

                    <p class="text-body-lg text-on-surface-variant leading-relaxed">
                        Browse verified Aba styles ready for bulk production. Low starter minimums (from 10 pcs), verified seam inspections, and direct waybills to your retail store or transport park.
                    </p>
                </div>

                {{-- Reference Photos WhatsApp Card --}}
                <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm flex flex-col gap-3 min-w-[280px] sm:min-w-[340px]">
                    <div class="flex items-center gap-2.5 text-secondary">
                        <x-ui.icon name="photo_camera" class="text-[24px]" />
                        <h2 class="text-headline-sm font-semibold text-on-surface">Have reference photos?</h2>
                    </div>
                    <p class="text-body-md text-on-surface-variant">
                        Send pictures from Instagram, TikTok, or your own samples to get instant bulk sourcing quotes.
                    </p>
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('open-custom-photo-request')"
                        class="inline-flex min-h-11 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-lg font-bold hover:bg-secondary/90 transition-colors shadow-sm"
                    >
                        <x-ui.icon name="add_photo_alternate" class="text-[20px]" />
                        <span>Send Photos for a Quote</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Search & Filter Controls --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 w-full">
        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-2xl shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-center">
                {{-- Search Input --}}
                <div class="md:col-span-6 relative">
                    <x-ui.icon name="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px]" />
                    <input
                        wire:model.live.debounce.300ms="search"
                        class="w-full min-h-11 pl-11 pr-4 py-2.5 rounded-lg bg-surface-container-low text-on-surface text-body-md placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary border-0 transition-colors"
                        placeholder="Search shirts, dresses, senator styles, footwear, bags..."
                        type="search"
                        aria-label="Search products"
                    />
                </div>

                {{-- Sort Dropdown --}}
                <div class="md:col-span-3">
                    <div class="relative">
                        <select
                            wire:model.live="sort"
                            class="w-full min-h-11 appearance-none pl-3.5 pr-10 py-2.5 rounded-lg bg-surface-container-low text-on-surface text-body-md focus:outline-none focus:ring-2 focus:ring-primary border-0 cursor-pointer"
                            aria-label="Sort products"
                        >
                            <option value="default">Sort: Featured &amp; Newest</option>
                            <option value="price-low">Sort: Price: Low to High</option>
                            <option value="price-high">Sort: Price: High to Low</option>
                            <option value="moq-low">Sort: Lowest MOQ First</option>
                        </select>
                        <x-ui.icon name="expand_more" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-outline text-[18px]" />
                    </div>
                </div>

                {{-- Price Filter Dropdown --}}
                <div class="md:col-span-3">
                    <div class="relative">
                        <select
                            wire:model.live="priceRange"
                            class="w-full min-h-11 appearance-none pl-3.5 pr-10 py-2.5 rounded-lg bg-surface-container-low text-on-surface text-body-md focus:outline-none focus:ring-2 focus:ring-primary border-0 cursor-pointer"
                            aria-label="Filter by price range"
                        >
                            <option value="">All Price Ranges</option>
                            <option value="under15">Under ₦15,000 / pc</option>
                            <option value="15to25">₦15,000 – ₦25,000 / pc</option>
                            <option value="above25">Above ₦25,000 / pc</option>
                        </select>
                        <x-ui.icon name="tune" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-outline text-[18px]" />
                    </div>
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 scrollbar-none" id="categoryTabs" role="tablist">
                <button
                    type="button"
                    wire:click="selectCategory('')"
                    role="tab"
                    aria-selected="{{ $categorySlug === '' ? 'true' : 'false' }}"
                    @class([
                        'min-h-11 px-4 py-2 rounded-lg text-label-md font-semibold whitespace-nowrap transition-all flex items-center gap-1.5',
                        'bg-primary-container text-on-primary-container shadow-xs' => $categorySlug === '',
                        'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high' => $categorySlug !== '',
                    ])
                >
                    <span>All Products</span>
                    <span class="text-xs opacity-80">({{ $products->total() }})</span>
                </button>

                @foreach ($categories as $category)
                    <button
                        type="button"
                        wire:click="selectCategory('{{ $category->slug }}')"
                        role="tab"
                        aria-selected="{{ $categorySlug === $category->slug ? 'true' : 'false' }}"
                        @class([
                            'min-h-11 px-4 py-2 rounded-lg text-label-md font-semibold whitespace-nowrap transition-all flex items-center gap-1.5',
                            'bg-primary-container text-on-primary-container shadow-xs' => $categorySlug === $category->slug,
                            'bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high' => $categorySlug !== $category->slug,
                        ])
                    >
                        <span>{{ $category->name }}</span>
                        <span class="text-xs opacity-80">({{ $category->published_products_count }})</span>
                    </button>
                @endforeach
            </div>

            {{-- Active Filter Bar / Reset action --}}
            @if ($hasActiveFilters)
                <div class="pt-2 border-t border-outline-variant/30 flex items-center justify-between gap-3 text-body-sm text-on-surface-variant">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span>Showing {{ $products->total() }} results</span>
                        @if ($search)
                            <span class="inline-flex items-center gap-1 rounded bg-surface-container px-2 py-0.5 text-xs text-on-surface">
                                Search: "{{ $search }}"
                            </span>
                        @endif
                        @if ($categorySlug)
                            @php $activeCat = $categories->firstWhere('slug', $categorySlug); @endphp
                            <span class="inline-flex items-center gap-1 rounded bg-surface-container px-2 py-0.5 text-xs text-on-surface">
                                Category: {{ $activeCat?->name ?? $categorySlug }}
                            </span>
                        @endif
                        @if ($priceRange)
                            <span class="inline-flex items-center gap-1 rounded bg-surface-container px-2 py-0.5 text-xs text-on-surface">
                                Price filter active
                            </span>
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="min-h-11 px-3 flex items-center gap-1 text-primary hover:underline font-semibold text-label-sm shrink-0"
                    >
                        <x-ui.icon name="restart_alt" class="text-[16px]" />
                        <span>Clear All Filters</span>
                    </button>
                </div>
            @endif
        </div>
    </section>

    {{-- Product Grid Section --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2.5">
                <span class="text-headline-sm font-bold text-on-surface">Available Wholesale Styles</span>
                <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-label-sm font-semibold">
                    In Stock &amp; Production
                </span>
            </div>
            <p class="text-body-sm text-on-surface-variant hidden sm:block">
                All prices reflect verified direct factory rates
            </p>
        </div>

        {{-- Mobile: 2-column grid. Desktop: 3 to 4 column grid --}}
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6" id="productGrid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" wire:key="product-{{ $product->id }}" />
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-surface-container-lowest rounded-2xl p-10 sm:p-14 text-center max-w-lg mx-auto shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center mx-auto text-outline">
                    <x-ui.icon name="search_off" class="text-[32px]" />
                </div>
                <h3 class="text-headline-md font-bold text-on-surface">No products found</h3>
                <p class="text-body-md text-on-surface-variant">
                    We could not find any wholesale styles matching your current filters. Try searching with different terms or reset your filters.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="min-h-11 px-5 py-2.5 rounded-lg bg-primary-container text-on-primary-container font-label-lg font-bold hover:bg-primary transition-colors"
                    >
                        Reset All Filters
                    </button>
                    <a
                        href="{{ \App\Support\Whatsapp::url('Hello ' . config('abatradehub.name') . ', I am looking for a product not in the catalog.') }}"
                        target="_blank"
                        rel="noopener"
                        class="min-h-11 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary font-label-lg font-bold hover:bg-secondary/90 transition-colors inline-flex items-center justify-center gap-2"
                    >
                        <x-ui.icon name="chat" class="text-[18px]" />
                        <span>Ask on WhatsApp</span>
                    </a>
                </div>
            </div>
        @endif
    </section>
</div>

