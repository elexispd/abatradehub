<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    {{-- Search Form Card --}}
    <div class="rounded-3xl bg-surface-container-lowest p-6 sm:p-10 shadow-sm border border-outline-variant/30">
        <div class="max-w-2xl mx-auto text-center space-y-3 mb-8">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary-container text-on-primary-container text-label-sm font-bold tracking-wide uppercase">
                <x-ui.icon name="local_shipping" class="text-[17px] text-primary" />
                <span>Live Wholesale Tracking</span>
            </div>
            <h1 class="text-headline-md sm:text-headline-lg font-extrabold text-on-surface tracking-tight">
                Track Your Wholesale Order
            </h1>
            <p class="text-body-md text-on-surface-variant">
                Enter your invoice number to view live production progress, Ariaria 100% pre-shipment quality inspection, and transport waybill dispatch status.
            </p>
        </div>

        <form wire:submit="track" class="max-w-2xl mx-auto space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                {{-- Invoice Number Input --}}
                <div class="sm:col-span-8 relative">
                    <label for="track-invoice" class="sr-only">Invoice Number</label>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-on-surface-variant">
                        <x-ui.icon name="receipt_long" class="text-[20px]" />
                    </div>
                    <input
                        id="track-invoice"
                        type="text"
                        wire:model="invoice"
                        placeholder="e.g. ATH-INV-2026-0005"
                        autocomplete="off"
                        autocapitalize="characters"
                        class="w-full rounded-2xl border border-outline-variant/60 bg-surface pl-11 pr-4 py-3.5 text-body-md font-semibold tracking-wide text-on-surface placeholder:text-outline placeholder:font-normal focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all uppercase"
                    />
                </div>

                {{-- Track Button --}}
                <div class="sm:col-span-4">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full h-full min-h-[52px] inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3.5 text-label-lg font-bold text-on-primary shadow-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 active:scale-[0.99] transition-all disabled:opacity-60 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="track" class="inline-flex items-center gap-2">
                            <x-ui.icon name="search" class="text-[20px]" />
                            <span>Track Order</span>
                        </span>
                        <span wire:loading wire:target="track" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-on-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Checking...</span>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Optional Phone / Email Verification for Security --}}
            <div x-data="{ showExtra: {{ !empty($phone) ? 'true' : 'false' }} }" class="text-center pt-1">
                <button
                    type="button"
                    x-on:click="showExtra = !showExtra"
                    class="text-label-sm font-medium text-on-surface-variant hover:text-primary inline-flex items-center gap-1.5 transition-colors"
                >
                    <x-ui.icon name="lock" class="text-[14px]" />
                    <span x-text="showExtra ? 'Hide optional phone verification' : 'Verify with phone or email (optional)'"></span>
                    <x-ui.icon name="expand_more" class="text-[16px] transition-transform" x-bind:class="{ 'rotate-180': showExtra }" />
                </button>

                <div x-show="showExtra" x-cloak x-transition.opacity class="mt-3 max-w-md mx-auto text-left">
                    <label for="track-phone" class="block text-label-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1">
                        Phone Number or Email (Associated with Order)
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-on-surface-variant">
                            <x-ui.icon name="phone" class="text-[18px]" />
                        </div>
                        <input
                            id="track-phone"
                            type="text"
                            wire:model="phone"
                            placeholder="+234... or customer@example.com"
                            class="w-full rounded-xl border border-outline-variant/60 bg-surface pl-10 pr-4 py-2.5 text-body-sm text-on-surface placeholder:text-outline focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                        />
                    </div>
                </div>
            </div>
        </form>

        {{-- Error Banner --}}
        @if ($errorMessage)
            <div class="mt-6 max-w-2xl mx-auto rounded-2xl border border-error/20 bg-error-container/15 p-4 text-on-error-container flex items-start gap-3">
                <x-ui.icon name="error" class="text-[22px] text-error shrink-0 mt-0.5" />
                <div class="space-y-1 text-body-sm">
                    <p class="font-semibold text-error">{{ $errorMessage }}</p>
                    <p class="text-on-surface-variant text-body-xs">
                        Tip: You can find your invoice code in your email receipt (e.g. <code>ATH-INV-2026-0005</code>) or WhatsApp confirmation.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Order Details Section (When Order is Found) --}}
    @if ($sale)
        <div class="space-y-8 animate-fadeIn" x-data="{ copied: false }">
            {{-- Order Header Card --}}
            <div class="rounded-3xl bg-surface-container-lowest p-6 sm:p-8 shadow-sm border border-outline-variant/30 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-outline-variant/20">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span class="text-label-sm font-semibold uppercase tracking-wider text-on-surface-variant">Wholesale Invoice</span>
                            <span class="text-outline-variant/60">&bull;</span>
                            <span class="text-label-sm text-on-surface-variant">
                                Placed {{ $sale->order_date?->format('M j, Y') ?? $sale->created_at?->format('M j, Y') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <h2 class="text-headline-sm sm:text-headline-md font-extrabold text-on-surface tracking-tight font-mono">
                                {{ $sale->invoice_number }}
                            </h2>

                            {{-- Copy Invoice Button --}}
                            <button
                                type="button"
                                x-on:click="navigator.clipboard.writeText('{{ $sale->invoice_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-label-xs font-semibold text-primary hover:bg-primary-container/20 transition-all border border-primary/20"
                                title="Copy invoice number"
                            >
                                <x-ui.icon name="content_copy" class="text-[14px]" x-show="!copied" />
                                <x-ui.icon name="check" class="text-[14px] text-secondary" x-show="copied" x-cloak />
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        {{-- Fulfillment Status Badge --}}
                        @php
                            $status = $sale->status instanceof \App\Enums\SaleStatus ? $sale->status : \App\Enums\SaleStatus::tryFrom((string)$sale->status);
                            $badgeClasses = match($status) {
                                \App\Enums\SaleStatus::PendingPayment => 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-800',
                                \App\Enums\SaleStatus::Processing => 'bg-sky-100 text-sky-900 border-sky-300 dark:bg-sky-950/40 dark:text-sky-200 dark:border-sky-800',
                                \App\Enums\SaleStatus::Inspection => 'bg-primary-container text-on-primary-container border-primary/30',
                                \App\Enums\SaleStatus::Dispatched => 'bg-indigo-100 text-indigo-900 border-indigo-300 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-800',
                                \App\Enums\SaleStatus::Delivered, \App\Enums\SaleStatus::Completed => 'bg-secondary-container text-secondary border-secondary/30',
                                \App\Enums\SaleStatus::Cancelled => 'bg-rose-100 text-rose-900 border-rose-300 dark:bg-rose-950/40 dark:text-rose-200 dark:border-rose-800',
                                default => 'bg-surface-container text-on-surface border-outline-variant/30',
                            };
                            $badgeIcon = match($status) {
                                \App\Enums\SaleStatus::PendingPayment => 'schedule',
                                \App\Enums\SaleStatus::Processing => 'storefront',
                                \App\Enums\SaleStatus::Inspection => 'verified',
                                \App\Enums\SaleStatus::Dispatched => 'local_shipping',
                                \App\Enums\SaleStatus::Delivered, \App\Enums\SaleStatus::Completed => 'task_alt',
                                \App\Enums\SaleStatus::Cancelled => 'cancel',
                                default => 'inventory_2',
                            };
                        @endphp

                        <span class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-label-sm font-bold border {{ $badgeClasses }}">
                            <x-ui.icon :name="$badgeIcon" class="text-[16px]" />
                            <span>{{ $status?->getLabel() ?? 'Processing' }}</span>
                        </span>

                        {{-- Payment Badge --}}
                        @php
                            $paymentStatus = $sale->payment_status instanceof \App\Enums\PaymentStatus ? $sale->payment_status : \App\Enums\PaymentStatus::tryFrom((string)$sale->payment_status);
                        @endphp
                        @if ($paymentStatus === \App\Enums\PaymentStatus::Paid)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-label-sm font-bold bg-secondary-container/60 text-secondary border border-secondary/20">
                                <x-ui.icon name="check_circle" class="text-[15px]" />
                                <span>Paid (₦{{ number_format($sale->amount_paid, 2) }})</span>
                            </span>
                        @elseif ($paymentStatus === \App\Enums\PaymentStatus::Partial)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-label-sm font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                <x-ui.icon name="pie_chart" class="text-[15px]" />
                                <span>Partially Paid (Bal: ₦{{ number_format($sale->balance_due, 2) }})</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-label-sm font-bold bg-surface-container text-on-surface-variant border border-outline-variant/30">
                                <x-ui.icon name="pending" class="text-[15px]" />
                                <span>Unpaid (₦{{ number_format($sale->total_amount, 2) }})</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Cancellation Alert if Cancelled --}}
                @if ($status === \App\Enums\SaleStatus::Cancelled)
                    <div class="rounded-2xl border border-error/30 bg-error-container/20 p-5 text-on-error-container space-y-2">
                        <div class="flex items-center gap-2 font-bold text-error">
                            <x-ui.icon name="cancel" class="text-[20px]" />
                            <span>Order Cancelled</span>
                        </div>
                        <p class="text-body-sm text-on-surface-variant">
                            This wholesale order was marked as cancelled. If you believe this is in error or require a settlement inquiry, please reach out to our client care team on WhatsApp below.
                        </p>
                    </div>
                @endif

                {{-- Milestone Stepper (Visual Fulfillment Pipeline) --}}
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-label-lg font-bold text-on-surface uppercase tracking-wider text-xs text-on-surface-variant">
                            Fulfillment &amp; Quality Inspection Pipeline
                        </h3>
                        <span class="text-label-xs font-semibold text-secondary inline-flex items-center gap-1">
                            <x-ui.icon name="verified" class="text-[14px]" />
                            <span>100% Ariaria QC Guarantee</span>
                        </span>
                    </div>

                    {{-- Horizontal Stepper (Desktop) / Vertical (Mobile) --}}
                    <div class="relative py-4">
                        {{-- Desktop Steps Container --}}
                        <div class="hidden md:grid md:grid-cols-5 gap-2 relative">
                            {{-- Connecting Line --}}
                            <div class="absolute top-5 left-1/10 right-1/10 h-1 bg-outline-variant/30 -z-0" aria-hidden="true"></div>

                            @foreach ($stages as $index => $stage)
                                <div class="relative flex flex-col items-center text-center px-1 z-10">
                                    {{-- Step Icon Node --}}
                                    @if ($stage['is_completed'] && !$stage['is_current'])
                                        {{-- Completed Step --}}
                                        <div class="h-10 w-10 rounded-full bg-secondary text-on-secondary flex items-center justify-center shadow-sm">
                                            <x-ui.icon name="check" class="text-[20px] font-bold" />
                                        </div>
                                    @elseif ($stage['is_current'])
                                        {{-- Current Active Step (Pulsing Glow) --}}
                                        <div class="relative flex items-center justify-center">
                                            <span class="animate-ping absolute inline-flex h-11 w-11 rounded-full bg-primary/30"></span>
                                            <div class="h-10 w-10 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md relative">
                                                <x-ui.icon :name="$stage['icon']" class="text-[20px]" />
                                            </div>
                                        </div>
                                    @else
                                        {{-- Pending Future Step --}}
                                        <div class="h-10 w-10 rounded-full bg-surface-container border-2 border-outline-variant/50 text-on-surface-variant/70 flex items-center justify-center">
                                            <x-ui.icon :name="$stage['icon']" class="text-[18px]" />
                                        </div>
                                    @endif

                                    {{-- Stage Title & Details --}}
                                    <div class="mt-3 space-y-1">
                                        <p @class([
                                            'text-label-md font-bold',
                                            'text-primary font-extrabold' => $stage['is_current'],
                                            'text-secondary font-bold' => $stage['is_completed'] && !$stage['is_current'],
                                            'text-on-surface-variant/70 font-medium' => !$stage['is_completed'] && !$stage['is_current'],
                                        ])>
                                            {{ $stage['title'] }}
                                        </p>

                                        @if ($stage['timestamp'])
                                            <span class="inline-block text-label-xs text-on-surface-variant font-medium">
                                                {{ $stage['timestamp'] }}
                                            </span>
                                        @elseif ($stage['is_current'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-container px-2 py-0.5 text-label-xs font-bold text-on-primary-container">
                                                In Progress
                                            </span>
                                        @else
                                            <span class="text-label-xs text-outline font-normal">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Mobile Vertical Stepper --}}
                        <div class="md:hidden space-y-4 relative pl-8 before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-outline-variant/40">
                            @foreach ($stages as $stage)
                                <div class="relative">
                                    {{-- Bullet Icon --}}
                                    <div @class([
                                        'absolute -left-8 top-0.5 flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                                        'bg-secondary text-on-secondary shadow-xs' => $stage['is_completed'] && !$stage['is_current'],
                                        'bg-primary text-on-primary ring-4 ring-primary/20 shadow-sm' => $stage['is_current'],
                                        'bg-surface-container border border-outline-variant text-on-surface-variant' => !$stage['is_completed'] && !$stage['is_current'],
                                    ])>
                                        @if ($stage['is_completed'] && !$stage['is_current'])
                                            <x-ui.icon name="check" class="text-[16px]" />
                                        @else
                                            <x-ui.icon :name="$stage['icon']" class="text-[15px]" />
                                        @endif
                                    </div>

                                    {{-- Text --}}
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <h4 @class([
                                                'text-label-md font-bold',
                                                'text-primary' => $stage['is_current'],
                                                'text-on-surface' => $stage['is_completed'] && !$stage['is_current'],
                                                'text-on-surface-variant/70' => !$stage['is_completed'] && !$stage['is_current'],
                                            ])>
                                                {{ $stage['title'] }}
                                            </h4>

                                            @if ($stage['is_current'])
                                                <span class="rounded bg-primary-container px-1.5 py-0.5 text-[11px] font-bold text-on-primary-container">
                                                    Current
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-body-xs text-on-surface-variant">{{ $stage['description'] }}</p>

                                        @if ($stage['timestamp'])
                                            <p class="text-[11px] font-semibold text-secondary">{{ $stage['timestamp'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Delivery Destination & Recipient Meta --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-outline-variant/20 bg-surface-container-low/50 rounded-2xl p-4">
                    <div>
                        <span class="text-label-xs uppercase tracking-wider text-on-surface-variant block font-semibold">Wholesale Recipient</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <strong class="text-body-md font-bold text-on-surface block">{{ $this->maskedCustomerName }}</strong>
                            @if ($isVerified)
                                <span class="inline-flex items-center gap-1 rounded-full bg-secondary-container px-2 py-0.5 text-label-xs font-bold text-secondary" title="Verified Buyer">
                                    <x-ui.icon name="verified" class="text-[13px]" />
                                    <span>Verified</span>
                                </span>
                            @endif
                        </div>
                        @if ($isVerified && $sale->customer_company)
                            <span class="text-body-xs text-on-surface-variant block">{{ $sale->customer_company }}</span>
                        @elseif (!$isVerified)
                            <span class="text-body-xs text-outline block mt-0.5">Buyer identity masked for privacy</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-label-xs uppercase tracking-wider text-on-surface-variant block font-semibold">Destination City &amp; Country</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-ui.icon name="location_on" class="text-[18px] text-primary shrink-0" />
                            <strong class="text-body-md font-bold text-on-surface">
                                {{ $sale->destination_city }}, {{ $sale->destination_country }}
                            </strong>
                        </div>
                        @if ($this->maskedShippingAddress)
                            <span class="text-body-xs text-on-surface-variant block mt-0.5 truncate" title="{{ $this->maskedShippingAddress }}">
                                {{ $this->maskedShippingAddress }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <span class="text-label-xs uppercase tracking-wider text-on-surface-variant block font-semibold">Logistics Method</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-ui.icon name="directions_bus" class="text-[18px] text-secondary shrink-0" />
                            <strong class="text-body-md font-bold text-on-surface">Interstate Park / Waybill Haulage</strong>
                        </div>
                        <span class="text-body-xs text-on-surface-variant block mt-0.5">Dispatched directly from Aba commercial hub</span>
                    </div>
                </div>
            </div>

            {{-- Purchased Wholesale Items Summary --}}
            <div class="rounded-3xl bg-surface-container-lowest p-6 sm:p-8 shadow-sm border border-outline-variant/30 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-label-lg font-bold text-on-surface uppercase tracking-wider text-xs text-on-surface-variant">
                        Purchased Wholesale Products ({{ $sale->items->count() }} {{ \Illuminate\Support\Str::plural('Item', $sale->items->count()) }})
                    </h3>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-outline-variant/30">
                    <table class="w-full text-left text-body-sm">
                        <thead class="bg-surface-container text-label-sm font-semibold text-on-surface">
                            <tr>
                                <th class="px-4 py-3">Product Description</th>
                                <th class="px-4 py-3 text-center">SKU</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Unit Rate</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20 bg-surface-container-lowest">
                            @foreach ($sale->items as $item)
                                @php
                                    $prod = $item->product;
                                    $media = $prod?->getFirstMedia('images');
                                    $img = $media ? ($media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl()) : null;
                                @endphp
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            @if ($img)
                                                <img src="{{ $img }}" alt="{{ $item->item_name }}" class="h-12 w-12 rounded-lg object-cover border border-outline-variant/30 shrink-0" />
                                            @else
                                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-surface-container text-primary">
                                                    <x-ui.icon name="inventory_2" class="text-[22px]" />
                                                </div>
                                            @endif
                                            <div>
                                                @if ($prod)
                                                    <a href="{{ route('products.show', ['category' => $prod->category?->slug ?? 'all', 'product' => $prod->slug]) }}" target="_blank" class="font-bold text-on-surface hover:text-primary transition-colors">
                                                        {{ $item->item_name }}
                                                    </a>
                                                @else
                                                    <span class="font-bold text-on-surface">{{ $item->item_name }}</span>
                                                @endif
                                                @if ($item->specifications)
                                                    <p class="text-body-xs text-on-surface-variant">{{ $item->specifications }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono text-xs text-on-surface-variant">
                                        {{ $item->item_sku ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-semibold text-on-surface">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-medium text-on-surface">
                                        ₦{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-bold text-primary">
                                        ₦{{ number_format($item->total_price, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-surface-container-low/60 text-body-sm font-semibold divide-y divide-outline-variant/20">
                            <tr>
                                <td colspan="4" class="px-4 py-2.5 text-right text-on-surface-variant">Subtotal:</td>
                                <td class="px-4 py-2.5 text-right font-bold text-on-surface">₦{{ number_format($sale->subtotal, 2) }}</td>
                            </tr>
                            @if ((float) $sale->shipping_fee > 0)
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right text-on-surface-variant">Interstate Logistics / Haulage:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-on-surface">₦{{ number_format($sale->shipping_fee, 2) }}</td>
                                </tr>
                            @endif
                            @if ((float) $sale->inspection_fee > 0)
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right text-on-surface-variant">Pre-Shipment Inspection:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-on-surface">₦{{ number_format($sale->inspection_fee, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="bg-surface-container text-body-md font-bold">
                                <td colspan="4" class="px-4 py-3 text-right text-on-surface">Total Wholesale Amount:</td>
                                <td class="px-4 py-3 text-right font-extrabold text-primary">₦{{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Audit Trail Timeline (Real Logged Milestones) --}}
            @if (count($auditTimeline) > 0)
                <div class="rounded-3xl bg-surface-container-lowest p-6 sm:p-8 shadow-sm border border-outline-variant/30 space-y-4">
                    <h3 class="text-label-lg font-bold text-on-surface uppercase tracking-wider text-xs text-on-surface-variant">
                        Shipment &amp; Milestone Activity Log
                    </h3>

                    <div class="relative pl-6 sm:pl-8 space-y-4 before:absolute before:left-2.5 sm:before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-outline-variant/40">
                        @foreach ($auditTimeline as $activity)
                            <div class="relative">
                                {{-- Bullet --}}
                                <div class="absolute -left-6 sm:-left-8 top-0.5 flex h-5 w-5 sm:h-6 sm:w-6 items-center justify-center rounded-full bg-primary-container text-primary">
                                    <x-ui.icon :name="$activity['icon']" class="text-[14px]" />
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-label-sm font-bold text-on-surface">{{ $activity['title'] }}</span>
                                        <span class="text-outline-variant/60">&bull;</span>
                                        <span class="text-label-xs text-on-surface-variant font-medium">{{ $activity['formatted_time'] }}</span>
                                    </div>
                                    <p class="text-body-xs text-on-surface-variant">{{ $activity['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Support & Action CTA --}}
            @php
                $waMessage = "Hello " . config('abatradehub.name') . ", I am tracking my wholesale order {$sale->invoice_number} (Status: " . ($status?->getLabel() ?? 'Processing') . "). Customer: {$sale->customer_name}. Please I would like to get an update on delivery.";
                $waUrl = \App\Support\Whatsapp::url($waMessage);
            @endphp

            <div class="rounded-3xl bg-gradient-to-br from-surface-container-high to-surface-container-lowest p-6 sm:p-8 border border-outline-variant/30 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="space-y-1 text-center sm:text-left">
                    <h4 class="text-headline-xs sm:text-headline-sm font-bold text-on-surface">
                        Have Questions About This Shipment?
                    </h4>
                    <p class="text-body-sm text-on-surface-variant max-w-xl">
                        Our Ariaria sourcing &amp; transport logistics team is available on WhatsApp to answer waybill dispatch and delivery arrival queries.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a
                        href="{{ $waUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-2.5 text-label-md font-bold text-on-secondary shadow-sm hover:bg-secondary/90 transition-all active:scale-[0.98]"
                    >
                        <x-ui.icon name="chat" class="text-[18px]" />
                        <span>Chat on WhatsApp</span>
                    </a>

                    @if ($isVerified && $this->signedReceiptUrl)
                        <a
                            href="{{ $this->signedReceiptUrl }}"
                            target="_blank"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-surface-container-high px-5 py-2.5 text-label-md font-bold text-on-surface hover:bg-surface-container-highest transition-all border border-outline-variant/40"
                        >
                            <x-ui.icon name="receipt" class="text-[18px]" />
                            <span>View Official Receipt</span>
                        </a>
                    @elseif (!$isVerified && ($sale->payments->count() > 0 || (float) $sale->amount_paid > 0))
                        <button
                            type="button"
                            x-on:click="document.getElementById('track-phone')?.focus()"
                            class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-surface-container px-4 py-2 text-label-xs font-semibold text-on-surface-variant hover:text-primary transition-all border border-dashed border-outline-variant/60 cursor-pointer"
                            title="Verify phone or email to view the official receipt"
                        >
                            <x-ui.icon name="lock" class="text-[15px] text-primary" />
                            <span>Verify Phone to View Receipt</span>
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="resetSearch"
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl border border-outline-variant/60 px-4 py-2.5 text-label-md font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all cursor-pointer"
                    >
                        <x-ui.icon name="restart_alt" class="text-[18px]" />
                        <span>Track Another</span>
                    </button>
                </div>
            </div>
        </div>
    @elseif (!$searched)
        {{-- Default Guidance Section (Before Search) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
            <div class="rounded-2xl bg-surface-container-lowest p-6 border border-outline-variant/30 space-y-3 shadow-xs">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-container text-primary">
                    <x-ui.icon name="receipt_long" class="text-[26px]" />
                </div>
                <h3 class="text-headline-xs font-bold text-on-surface">Find Your Invoice Code</h3>
                <p class="text-body-sm text-on-surface-variant">
                    Your invoice reference (e.g. <code>ATH-INV-2026-0005</code>) is located at the top of your order receipt and in your payment confirmation email.
                </p>
            </div>

            <div class="rounded-2xl bg-surface-container-lowest p-6 border border-outline-variant/30 space-y-3 shadow-xs">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary-container text-secondary">
                    <x-ui.icon name="verified" class="text-[26px]" />
                </div>
                <h3 class="text-headline-xs font-bold text-on-surface">100% Pre-Shipment QC</h3>
                <p class="text-body-sm text-on-surface-variant">
                    Every wholesale carton is verified for stitching, fabric authenticity, sizing, and quantity at our Ariaria quality desk before carrier dispatch.
                </p>
            </div>

            <div class="rounded-2xl bg-surface-container-lowest p-6 border border-outline-variant/30 space-y-3 shadow-xs">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-container-highest text-primary">
                    <x-ui.icon name="local_shipping" class="text-[26px]" />
                </div>
                <h3 class="text-headline-xs font-bold text-on-surface">Park Waybill &amp; Delivery</h3>
                <p class="text-body-sm text-on-surface-variant">
                    Interstate haulage delivers to commercial transport terminals across all 36 Nigerian states and air cargo hubs across Africa.
                </p>
            </div>
        </div>
    @endif
</div>
