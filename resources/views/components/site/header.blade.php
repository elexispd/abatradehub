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
    {{-- Announcement bar --}}
    <div class="border-b border-outline-variant/30 bg-surface-container-high px-4 py-1 text-center">
        <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 text-label-sm text-on-surface-variant">
            <x-ui.icon name="local_shipping" class="hidden shrink-0 text-[15px] text-primary sm:inline-block" />
            <span class="truncate sm:hidden">Bulk order support on WhatsApp</span>
            <span class="hidden truncate sm:inline">{{ config('abatradehub.topbar') }}</span>
        </div>
    </div>

    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:h-20 lg:px-8">
        {{-- Wordmark --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="{{ config('abatradehub.name') }} home">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-container text-on-primary-container">
                <x-ui.icon name="storefront" class="text-[24px]" />
            </span>
            <span class="flex flex-col text-left leading-tight">
                <span class="text-headline-sm font-bold tracking-tight text-on-surface">{{ config('abatradehub.name') }}</span>
                <span class="text-label-sm uppercase tracking-wider text-primary">{{ config('abatradehub.tagline') }}</span>
            </span>
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
