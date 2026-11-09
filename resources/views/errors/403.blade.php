@php
    $crumbs = [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => '403 Access Restricted'],
    ];
@endphp

<x-layouts.public
    title="403 - Access Restricted"
    description="Access to this resource or wholesale document is restricted on AbaTradeHub."
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
                        403
                    </span>
                </div>

                {{-- Content Layer --}}
                <div class="relative z-10 space-y-4">
                    {{-- Lock Icon Badge --}}
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-error-container/40 text-error shadow-xs ring-8 ring-error-container/20">
                        <x-ui.icon name="lock" class="text-[40px]" />
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-error-container/30 text-on-error-container text-label-xs font-bold uppercase tracking-wider">
                        <span>Error 403 &bull; Authorization Required</span>
                    </div>

                    <h1 class="text-headline-md sm:text-headline-lg font-extrabold text-on-surface tracking-tight">
                        Access to This Resource is Restricted
                    </h1>

                    @php
                        $rawMsg = $exception?->getMessage();
                        $isTechnical = $rawMsg && (
                            str_contains($rawMsg, 'App\\') ||
                            str_contains($rawMsg, 'model') ||
                            str_contains($rawMsg, 'SQL') ||
                            str_contains($rawMsg, 'Exception')
                        );
                        $userMessage = (!empty($rawMsg) && !$isTechnical)
                            ? $rawMsg
                            : 'You do not have authorization to view this receipt, confirmation, or restricted document directly.';
                    @endphp

                    <div class="max-w-xl mx-auto rounded-2xl bg-surface-container-low p-4 text-body-sm text-on-surface border border-outline-variant/30">
                        <p class="font-medium">
                            {{ $userMessage }}
                        </p>
                    </div>
                </div>

                {{-- Contextual Guidance Card --}}
                <div class="relative z-10 max-w-xl mx-auto rounded-2xl bg-surface-container-low/80 p-5 sm:p-6 border border-outline-variant/30 text-left space-y-2">
                    <div class="flex items-center gap-2 font-bold text-on-surface text-label-md">
                        <x-ui.icon name="receipt_long" class="text-[20px] text-primary" />
                        <span>Looking for Your Official Receipt?</span>
                    </div>
                    <p class="text-body-xs text-on-surface-variant leading-relaxed">
                        Official wholesale receipts are cryptographically protected for customer privacy. Please access your receipt using the secure link in your payment confirmation email, or verify your phone on our order tracking page.
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <a
                        href="{{ route('orders.track') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-label-md font-bold text-on-primary shadow-md hover:bg-primary/90 transition-all active:scale-[0.98]"
                    >
                        <x-ui.icon name="local_shipping" class="text-[20px]" />
                        <span>Track Order &amp; Verify Identity</span>
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-surface-container px-6 py-3 text-label-md font-bold text-on-surface hover:bg-surface-container-high transition-all border border-outline-variant/40"
                    >
                        <x-ui.icon name="storefront" class="text-[20px] text-primary" />
                        <span>Browse Catalog</span>
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-1.5 rounded-xl border border-outline-variant/60 px-5 py-3 text-label-md font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-all"
                    >
                        <x-ui.icon name="home" class="text-[18px]" />
                        <span>Home</span>
                    </a>
                </div>

                {{-- Support Contact --}}
                <div class="relative z-10 pt-4 text-body-sm text-on-surface-variant">
                    Need help accessing your wholesale order or payment receipt?
                    <a
                        href="{{ \App\Support\Whatsapp::url('Hello ' . config('abatradehub.name') . ', I need assistance accessing my wholesale order receipt or account (Encountered 403 Forbidden).') }}"
                        target="_blank"
                        rel="noopener"
                        class="font-bold text-secondary hover:underline inline-flex items-center gap-1 ml-1"
                    >
                        <x-ui.icon name="chat" class="text-[16px]" />
                        <span>Contact Client Support on WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>

