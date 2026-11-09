@props([
    'heroProducts' => null,
    'minMoq' => null,
])

<section class="relative w-full overflow-hidden bg-surface-container-low pb-16 pt-8 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">
            {{-- Text & actions --}}
            <div class="space-y-8 lg:col-span-7">
                <div class="inline-flex items-center gap-2 rounded-full bg-surface-container-highest px-3 py-1.5 text-label-md text-primary shadow-sm">
                    <x-ui.icon name="verified" class="text-[18px]" />
                    <span>Direct Sourcing from Aba Manufacturers</span>
                </div>

                <div class="space-y-4">
                    <h1 class="text-display-lg-mobile font-bold tracking-tight text-on-surface lg:text-display-lg">
                        Quality Products. Wholesale Prices. Reliable Sourcing.
                    </h1>
                    <p class="max-w-xl text-body-lg leading-relaxed text-on-surface-variant">
                        We help retailers and resellers across Africa source in bulk from trusted manufacturers and traders in Aba, without the stress of travelling to crowded markets yourself.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <x-ui.button :href="route('home').'#quote'" size="lg" icon="arrow_forward" class="shadow-md active:scale-95">Request a Quote</x-ui.button>
                    <x-ui.button :href="url('/products')" variant="secondary" size="lg" icon="grid_view" icon-position="leading">Browse Catalogue</x-ui.button>
                </div>

                {{-- Trust micro-stats --}}
                <div class="grid grid-cols-1 gap-3 pt-6 sm:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-lg bg-surface-container-lowest p-3 shadow-sm">
                        <x-ui.icon name="inventory" class="shrink-0 text-[22px] text-secondary" />
                        <div>
                            <p class="text-label-md text-on-surface">Min. Order</p>
                            <p class="text-body-sm text-on-surface-variant">{{ $minMoq ? "From only {$minMoq} pcs" : 'Low starter minimums' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-surface-container-lowest p-3 shadow-sm">
                        <x-ui.icon name="fact_check" class="shrink-0 text-[22px] text-primary" />
                        <div>
                            <p class="text-label-md text-on-surface">Inspected</p>
                            <p class="text-body-sm text-on-surface-variant">Before dispatch</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-surface-container-lowest p-3 shadow-sm">
                        <x-ui.icon name="local_shipping" class="shrink-0 text-[22px] text-tertiary" />
                        <div>
                            <p class="text-label-md text-on-surface">Delivery</p>
                            <p class="text-body-sm text-on-surface-variant">Park &amp; doorstep</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visual: 2x2 collage of authentic Aba wholesale production & goods --}}
            <div class="relative lg:col-span-5">
                <div class="relative overflow-hidden rounded-2xl bg-surface-container shadow-xl ring-1 ring-outline-variant/10">
                    <div class="relative aspect-[4/3] w-full overflow-hidden sm:aspect-square">
                        <div class="grid h-full w-full grid-cols-2 gap-1 bg-surface-container-highest">
                            <div class="relative h-full w-full overflow-hidden bg-surface-container">
                                <img
                                    src="{{ asset('img/home1.jpg') }}"
                                    alt="Aba Wholesale Sourcing - Quality Apparel & Fashion"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
                                    loading="eager"
                                    fetchpriority="high"
                                />
                            </div>
                            <div class="relative h-full w-full overflow-hidden bg-surface-container">
                                <img
                                    src="{{ asset('img/home2.jpg') }}"
                                    alt="Aba Wholesale Sourcing - Footwear & Leathercraft"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
                                    loading="eager"
                                />
                            </div>
                            <div class="relative h-full w-full overflow-hidden bg-surface-container">
                                <img
                                    src="{{ asset('img/home3.jpg') }}"
                                    alt="Aba Wholesale Sourcing - Ready-to-Wear Textiles"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
                                    loading="lazy"
                                />
                            </div>
                            <div class="relative h-full w-full overflow-hidden bg-surface-container">
                                <img
                                    src="{{ asset('img/bags.webp') }}"
                                    alt="Aba Wholesale Sourcing - Wholesale Bags & Accessories"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
                                    loading="lazy"
                                />
                            </div>
                        </div>

                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-on-surface/80 via-transparent to-transparent"></div>

                        <div class="absolute inset-x-4 bottom-4 rounded-xl bg-surface/90 p-4 shadow-md backdrop-blur-md">
                            <div class="flex items-center gap-3">
                                <div class="h-2.5 w-2.5 animate-pulse rounded-full bg-secondary"></div>
                                <p class="text-label-md font-semibold text-on-surface">Sourced directly from makers in Aba</p>
                            </div>
                            <p class="mt-0.5 text-body-sm text-on-surface-variant">Ariaria &amp; Aba production hubs, Abia State</p>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 -z-10 h-32 w-32 rounded-2xl bg-primary-fixed opacity-60 blur-xl"></div>
            </div>
        </div>
    </div>
</section>

