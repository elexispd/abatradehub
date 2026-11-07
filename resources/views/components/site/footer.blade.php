@php
    $home = route('home');
    $links = [
        ['label' => 'Shop Catalogue', 'href' => url('/products')],
        ['label' => 'How It Works', 'href' => $home.'#how-it-works'],
        ['label' => 'Why Retailers Trust Us', 'href' => $home.'#why-us'],
        ['label' => 'Request a Quote', 'href' => $home.'#quote'],
    ];
    $guarantees = [
        ['icon' => 'verified_user', 'title' => '100% Pre-Shipment Inspection', 'text' => 'Fabric & stitching checks before release'],
        ['icon' => 'store', 'title' => 'Verified Suppliers', 'text' => 'Trusted Aba manufacturers & traders'],
        ['icon' => 'local_shipping', 'title' => 'Doorstep & Park Delivery', 'text' => 'Direct logistics across commercial parks & air'],
    ];
@endphp

<footer class="mt-16 w-full bg-surface-container pb-12 pt-16 text-on-surface shadow-footer">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 border-b border-outline-variant/30 pb-12 md:grid-cols-2 lg:grid-cols-4">
            {{-- About --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-container text-on-primary-container">
                        <x-ui.icon name="storefront" class="text-[22px]" />
                    </span>
                    <span class="text-headline-sm font-bold text-on-surface">{{ config('abatradehub.name') }}</span>
                </div>
                <p class="text-body-md leading-relaxed text-on-surface-variant">{{ config('abatradehub.description') }}</p>
                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-highest px-3 py-1 text-label-sm text-on-surface">
                        <x-ui.icon name="verified" class="text-[16px] text-secondary" /> Verified Suppliers
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-highest px-3 py-1 text-label-sm text-on-surface">
                        <x-ui.icon name="inventory_2" class="text-[16px] text-primary" /> Aba Sourced
                    </span>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="space-y-4">
                <h4 class="text-headline-sm font-semibold text-on-surface">Quick Links</h4>
                <ul class="space-y-1 text-body-md">
                    @foreach ($links as $link)
                        <li class="flex items-center gap-2">
                            <x-ui.icon name="chevron_right" class="text-[16px] text-primary" />
                            <a href="{{ $link['href'] }}" class="flex min-h-11 items-center text-on-surface-variant transition-colors hover:text-primary">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div class="space-y-4">
                <h4 class="text-headline-sm font-semibold text-on-surface">Support &amp; Contact</h4>
                <div class="space-y-3 text-body-md text-on-surface-variant">
                    <div class="flex items-start gap-2.5">
                        <x-ui.icon name="location_on" class="mt-0.5 shrink-0 text-[20px] text-primary" />
                        <span>{{ config('abatradehub.address') }}</span>
                    </div>
                    <a href="{{ \App\Support\Whatsapp::url() }}" target="_blank" rel="noopener" class="flex min-h-11 items-center gap-2.5 hover:text-secondary">
                        <x-ui.icon name="call" class="shrink-0 text-[20px] text-secondary" />
                        <span>{{ \App\Support\Whatsapp::display() }} (WhatsApp)</span>
                    </a>
                    <a href="mailto:{{ config('abatradehub.email') }}" class="flex min-h-11 items-center gap-2.5 hover:text-primary">
                        <x-ui.icon name="mail" class="shrink-0 text-[20px] text-primary" />
                        <span class="break-all">{{ config('abatradehub.email') }}</span>
                    </a>
                    <div class="flex items-start gap-2.5">
                        <x-ui.icon name="schedule" class="mt-0.5 shrink-0 text-[20px] text-primary" />
                        <span>{{ config('abatradehub.hours') }}</span>
                    </div>
                </div>
            </div>

            {{-- Guarantees --}}
            <div class="space-y-4">
                <h4 class="text-headline-sm font-semibold text-on-surface">Trust &amp; Guarantee</h4>
                <div class="space-y-3">
                    @foreach ($guarantees as $g)
                        <div class="flex items-start gap-3 rounded-lg bg-surface-container-lowest p-3 shadow-sm">
                            <x-ui.icon :name="$g['icon']" class="shrink-0 text-[22px] text-secondary" />
                            <div>
                                <p class="text-label-lg text-on-surface">{{ $g['title'] }}</p>
                                <p class="text-body-sm text-on-surface-variant">{{ $g['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-4 pt-8 text-body-sm text-on-surface-variant sm:flex-row">
            <p>&copy; {{ date('Y') }} {{ config('abatradehub.name') }}. Connecting Aba manufacturers with retailers across Africa.</p>
            <p>{{ config('abatradehub.name') }} is a sourcing intermediary, not a manufacturer.</p>
        </div>
    </div>
</footer>
