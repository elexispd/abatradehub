@php
    $crumbs = [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => '419 Session Expired'],
    ];
@endphp

<x-layouts.public
    title="419 - Session Expired"
    description="Your security session or form token has expired on AbaTradeHub."
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
                {{-- Decorative Watermark --}}
                <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden z-0" aria-hidden="true">
                    <span class="text-[160px] sm:text-[220px] font-black text-outline-variant/10 tracking-tighter select-none">
                        419
                    </span>
                </div>

                {{-- Content --}}
                <div class="relative z-10 space-y-4">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-100 text-amber-800 shadow-xs ring-8 ring-amber-50">
                        <x-ui.icon name="history" class="text-[40px]" />
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-label-xs font-bold uppercase tracking-wider">
                        <span>Error 419 &bull; Page Token Expired</span>
                    </div>

                    <h1 class="text-headline-md sm:text-headline-lg font-extrabold text-on-surface tracking-tight">
                        Your Session Has Expired
                    </h1>

                    <p class="text-body-md text-on-surface-variant max-w-lg mx-auto leading-relaxed">
                        For your security, wholesale cart tokens and form security tokens expire after a period of inactivity. Please reload the page to start a fresh secure session.
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        onclick="window.location.reload()"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-label-md font-bold text-on-primary shadow-md hover:bg-primary/90 transition-all active:scale-[0.98] cursor-pointer"
                    >
                        <x-ui.icon name="refresh" class="text-[20px]" />
                        <span>Refresh Page</span>
                    </button>

                    <a
                        href="{{ route('products.index') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-surface-container px-6 py-3 text-label-md font-bold text-on-surface hover:bg-surface-container-high transition-all border border-outline-variant/40"
                    >
                        <x-ui.icon name="storefront" class="text-[20px] text-primary" />
                        <span>Wholesale Catalog</span>
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-1.5 rounded-xl border border-outline-variant/60 px-5 py-3 text-label-md font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-all"
                    >
                        <x-ui.icon name="home" class="text-[18px]" />
                        <span>Home</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>

