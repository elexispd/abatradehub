@php
    $crumbs = [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => '404 Not Found'],
    ];
@endphp

<x-layouts.public
    title="404 - Page Not Found"
    description="The requested page or wholesale product could not be located on AbaTradeHub."
>
    <x-slot:head>
        <meta name="robots" content="noindex, follow">
    </x-slot:head>

    <div class="w-full pb-20 pt-4 sm:pt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <x-ui.breadcrumbs :items="$crumbs" />
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-surface-container-lowest p-8 sm:p-14 shadow-sm border border-outline-variant/30 text-center space-y-8 relative overflow-hidden">
                {{-- Decorative Background Watermark --}}
                <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden z-0" aria-hidden="true">
                    <span class="text-[160px] sm:text-[220px] font-black text-outline-variant/10 tracking-tighter select-none">
                        404
                    </span>
                </div>

                {{-- Content Layer --}}
                <div class="relative z-10 space-y-4">
                    {{-- Icon Badge --}}
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-primary-container text-primary shadow-xs">
                        <x-ui.icon name="search_off" class="text-[42px]" />
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant text-label-xs font-bold uppercase tracking-wider">
                        <span>Error 404 &bull; Item or Route Not Found</span>
                    </div>

                    <h1 class="text-headline-md sm:text-headline-lg font-extrabold text-on-surface tracking-tight">
                        We Couldn&rsquo;t Find That Page
                    </h1>

                    <p class="text-body-md text-on-surface-variant max-w-lg mx-auto leading-relaxed">
                        We couldn&rsquo;t locate this page or wholesale product. The link may have expired or moved to a new category.
                    </p>
                </div>

                {{-- Quick Action Buttons --}}
                <div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <a
                        href="{{ route('products.index') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-label-md font-bold text-on-primary shadow-md hover:bg-primary/90 transition-all active:scale-[0.98]"
                    >
                        <x-ui.icon name="storefront" class="text-[20px]" />
                        <span>Explore Wholesale Catalog</span>
                    </a>

                    <a
                        href="{{ route('orders.track') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-surface-container px-6 py-3 text-label-md font-bold text-on-surface hover:bg-surface-container-high transition-all border border-outline-variant/40"
                    >
                        <x-ui.icon name="local_shipping" class="text-[20px] text-primary" />
                        <span>Track an Order</span>
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-1.5 rounded-xl border border-outline-variant/60 px-5 py-3 text-label-md font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-all"
                    >
                        <x-ui.icon name="home" class="text-[18px]" />
                        <span>Home</span>
                    </a>
                </div>

                {{-- Popular Wholesale Categories Shortcuts --}}
                <div class="relative z-10 pt-6 border-t border-outline-variant/20 space-y-3">
                    <p class="text-label-xs uppercase tracking-wider font-bold text-on-surface-variant">
                        Popular Wholesale Categories
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <a href="{{ url('/products/footwear') }}" class="rounded-full bg-surface-container-low px-4 py-1.5 text-label-sm font-semibold text-on-surface hover:bg-primary hover:text-on-primary transition-all border border-outline-variant/30">
                            Footwear &amp; Shoes
                        </a>
                        <a href="{{ url('/products/clothing-fabrics') }}" class="rounded-full bg-surface-container-low px-4 py-1.5 text-label-sm font-semibold text-on-surface hover:bg-primary hover:text-on-primary transition-all border border-outline-variant/30">
                            Senator Sets &amp; Kaftans
                        </a>
                        <a href="{{ url('/products/leather-goods') }}" class="rounded-full bg-surface-container-low px-4 py-1.5 text-label-sm font-semibold text-on-surface hover:bg-primary hover:text-on-primary transition-all border border-outline-variant/30">
                            Leather Bags &amp; Belts
                        </a>
                        <a href="{{ url('/products') }}" class="rounded-full bg-surface-container-low px-4 py-1.5 text-label-sm font-semibold text-primary hover:bg-primary hover:text-on-primary transition-all border border-outline-variant/30">
                            View All Products &rarr;
                        </a>
                    </div>
                </div>

                {{-- Help Contact Footer --}}
                <div class="relative z-10 pt-4 text-body-sm text-on-surface-variant">
                    Need help finding a specific wholesale item?
                    <a
                        href="{{ \App\Support\Whatsapp::url('Hello ' . config('abatradehub.name') . ', I was looking for a wholesale product on your website but encountered a 404 page. Can you help me find it?') }}"
                        target="_blank"
                        rel="noopener"
                        class="font-bold text-secondary hover:underline inline-flex items-center gap-1 ml-1"
                    >
                        <x-ui.icon name="chat" class="text-[16px]" />
                        <span>Chat with Sourcing Desk</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>

