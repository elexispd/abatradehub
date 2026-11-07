@php
    $nav = [
        ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Shop / Catalogue', 'href' => url('/products'), 'active' => request()->is('products*')],
        ['label' => 'How It Works', 'href' => route('home').'#how-it-works', 'active' => false],
        ['label' => 'Why Us', 'href' => route('home').'#why-us', 'active' => false],
        ['label' => 'Contact', 'href' => route('home').'#quote', 'active' => false],
    ];
    $quoteUrl = route('home').'#quote';
@endphp

<header
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    class="fixed inset-x-0 top-0 z-50 bg-surface/90 shadow-header backdrop-blur-xl"
>
    {{-- Announcement bar (Continuous Marquee) --}}
    <div class="group relative flex overflow-hidden border-b border-outline-variant/30 bg-surface-container-high py-1.5 text-label-sm select-none" role="region" aria-label="Announcements">
        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-8 sm:w-16 bg-gradient-to-r from-surface-container-high to-transparent"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-8 sm:w-16 bg-gradient-to-l from-surface-container-high to-transparent"></div>

        <div class="flex w-max shrink-0 items-center animate-marquee group-hover:[animation-play-state:paused]">
            @for ($i = 0; $i < 2; $i++)
                <div class="flex shrink-0 items-center gap-6 sm:gap-8 px-4" @if($i === 1) aria-hidden="true" @endif>
                    <span class="inline-flex items-center gap-2 font-semibold text-on-surface">
                        <x-ui.icon name="local_shipping" class="text-[17px] text-primary" />
                        <span>Nationwide delivery across Nigeria &amp; international shipping across Africa</span>
                    </span>

                    <span class="text-outline-variant/60">&bull;</span>

                    <a
                        href="{{ \App\Support\Whatsapp::url('Hello ' . config('abatradehub.name') . ', I would like to make an enquiry about bulk wholesale orders and shipping.') }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-full bg-secondary/15 px-2.5 py-0.5 text-label-sm font-semibold text-secondary transition-all hover:bg-secondary hover:text-on-secondary"
                        tabindex="{{ $i === 1 ? -1 : 0 }}"
                    >
                        <x-ui.icon name="chat" class="text-[14px]" />
                        <span>Bulk order support on WhatsApp</span>
                        <x-ui.icon name="arrow_outward" class="text-[12px]" />
                    </a>

                    <span class="text-outline-variant/60">&bull;</span>

                    <span class="inline-flex items-center gap-1.5 font-medium text-on-surface-variant">
                        <x-ui.icon name="verified" class="text-[16px] text-secondary" />
                        <span>100% Pre-Shipment Quality Inspection Guaranteed</span>
                    </span>

                    <span class="text-outline-variant/60">&bull;</span>

                    <span class="inline-flex items-center gap-1.5 font-medium text-on-surface-variant">
                        <x-ui.icon name="storefront" class="text-[16px] text-primary" />
                        <span>Factory-Direct Wholesale &bull; Ariaria, Cemetery &amp; Ekeoha Markets</span>
                    </span>

                    <span class="text-outline-variant/60">&bull;</span>
                </div>
            @endfor
        </div>
    </div>

    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:h-20 lg:px-8">
        {{-- Wordmark & Logo --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="{{ config('abatradehub.name') }} home">
            <img src="{{ asset('img/logo.png') }}" alt="{{ config('abatradehub.name') }} logo" class="h-40 w-40 rounded-lg object-contain shadow-xs" />
        </a>

        {{-- Desktop navigation --}}
        <nav class="hidden items-center gap-6 xl:flex" aria-label="Main">
            @foreach ($nav as $item)
                <a
                    href="{{ $item['href'] }}"
                    @if ($item['active']) aria-current="page" @endif
                    @class([
                        'flex min-h-11 items-center py-2 text-label-lg transition-colors',
                        'font-bold text-primary' => $item['active'],
                        'text-on-surface-variant hover:text-on-surface' => ! $item['active'],
                    ])
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            
            <x-ui.button :href="$quoteUrl" size="sm">Get a Quote</x-ui.button>

            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-lg text-on-surface hover:bg-surface-container xl:hidden"
                x-on:click="open = ! open"
                x-bind:aria-expanded="open.toString()"
                aria-controls="mobile-menu"
                aria-label="Toggle menu"
            >
                <x-ui.icon name="menu" x-show="! open" />
                <x-ui.icon name="close" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <nav
        id="mobile-menu"
        x-show="open"
        x-cloak
        x-transition.opacity
        x-on:click.outside="open = false"
        class="border-t border-outline-variant/30 bg-surface xl:hidden"
        aria-label="Mobile"
    >
        <div class="mx-auto flex max-w-7xl flex-col px-4 py-2 sm:px-6">
            @foreach ($nav as $item)
                <a
                    href="{{ $item['href'] }}"
                    x-on:click="open = false"
                    @class([
                        'flex min-h-12 items-center border-b border-outline-variant/20 text-body-lg',
                        'font-bold text-primary' => $item['active'],
                        'text-on-surface' => ! $item['active'],
                    ])
                >{{ $item['label'] }}</a>
            @endforeach
            <div class="py-3">
                <x-ui.button :href="\App\Support\Whatsapp::url()" target="_blank" rel="noopener" variant="whatsapp" icon="chat" icon-position="leading" block>
                    Chat on WhatsApp
                </x-ui.button>
            </div>
        </div>
    </nav>
</header>
