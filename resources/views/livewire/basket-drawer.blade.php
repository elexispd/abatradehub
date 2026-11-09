<div>
    @if ($isOpen)
        <div
            class="fixed inset-0 z-50 flex justify-end bg-on-surface/50 backdrop-blur-xs transition-opacity"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="basketDrawerTitle"
        >
            {{-- Backdrop click --}}
            <div class="fixed inset-0" wire:click="close"></div>

            {{-- Drawer Container --}}
            <div class="relative w-full max-w-lg bg-surface-container-lowest shadow-2xl z-10 flex flex-col h-full max-h-screen overflow-hidden">
                {{-- Top Header --}}
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-outline-variant/30 bg-surface-container-low shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary text-on-secondary shadow-xs">
                            <x-ui.icon name="shopping_basket" class="text-[22px]" />
                        </div>
                        <div>
                            <h2 class="text-headline-sm font-bold text-on-surface" id="basketDrawerTitle">
                                Wholesale Basket
                            </h2>
                            <p class="text-label-sm text-on-surface-variant">
                                {{ count($items) }} {{ count($items) === 1 ? 'item' : 'items' }} &bull; {{ $totalUnits }} units
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="close"
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors"
                        aria-label="Close basket"
                    >
                        <x-ui.icon name="close" class="text-[22px]" />
                    </button>
                </div>

                {{-- Toast Notification if item was just added --}}
                @if ($toastMessage)
                    <div class="bg-secondary-container text-on-secondary-container px-4 py-2.5 text-body-sm font-semibold flex items-center justify-between shrink-0">
                        <span class="flex items-center gap-1.5">
                            <x-ui.icon name="check_circle" class="text-[18px] text-secondary" />
                            <span>{{ $toastMessage }}</span>
                        </span>
                        <button type="button" wire:click="$set('toastMessage', null)" class="text-on-secondary-container/70 hover:text-on-secondary-container">
                            <x-ui.icon name="close" class="text-[16px]" />
                        </button>
                    </div>
                @endif

                {{-- Error Banner --}}
                @if ($errorMessage)
                    <div class="m-4 rounded-xl bg-error-container p-3 text-body-sm text-on-error-container flex items-start gap-2 shrink-0">
                        <x-ui.icon name="error" class="text-[20px] shrink-0 text-error" />
                        <span>{{ $errorMessage }}</span>
                    </div>
                @endif

                {{-- Drawer Body Content --}}
                <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">
                    @if ($step === 'basket')
                        {{-- BASKET VIEW --}}
                        @if (empty($items))
                            <div class="py-16 text-center space-y-4">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-surface-container text-outline">
                                    <x-ui.icon name="shopping_basket" class="text-[36px]" />
                                </div>
                                <h3 class="text-headline-sm font-bold text-on-surface">Your wholesale basket is empty</h3>
                                <p class="text-body-md text-on-surface-variant max-w-xs mx-auto">
                                    Browse verified Aba styles and add products meeting wholesale minimum order quantities.
                                </p>
                                <div class="pt-2">
                                    <button
                                        type="button"
                                        wire:click="close"
                                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-primary-container px-5 py-2.5 text-label-lg font-bold text-on-primary-container shadow-xs hover:bg-primary transition-colors"
                                    >
                                        Browse Wholesale Catalog
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Items List --}}
                            <div class="space-y-3">
                                @foreach ($items as $pid => $item)
                                    <div class="flex gap-3 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/30 transition-all">
                                        {{-- Image Thumbnail --}}
                                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-surface-container">
                                            @if (! empty($item['primary_image']))
                                                <img src="{{ $item['primary_image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover" />
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-outline">
                                                    <x-ui.icon name="image" class="text-[24px]" />
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Item Meta & Controls --}}
                                        <div class="flex-1 flex flex-col justify-between min-w-0">
                                            <div>
                                                <div class="flex items-start justify-between gap-2">
                                                    <h4 class="text-body-md font-bold text-on-surface line-clamp-1" title="{{ $item['name'] }}">
                                                        {{ $item['name'] }}
                                                    </h4>
                                                    <button
                                                        type="button"
                                                        wire:click="removeItem({{ $pid }})"
                                                        class="text-outline hover:text-error transition-colors p-0.5"
                                                        title="Remove from basket"
                                                    >
                                                        <x-ui.icon name="delete" class="text-[18px]" />
                                                    </button>
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-on-surface-variant mt-0.5">
                                                    <span>SKU: {{ $item['sku'] ?: 'ATH-item' }}</span>
                                                    <span>&bull;</span>
                                                    <span>MOQ: {{ $item['moq'] }} {{ $item['moq_unit'] }}</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between gap-2 mt-2 pt-1 border-t border-outline-variant/20">
                                                {{-- Quantity Controller (Step 1, MOQ enforced) --}}
                                                <div class="flex items-center gap-1.5">
                                                    <button
                                                        type="button"
                                                        wire:click="decrement({{ $pid }}, 1)"
                                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed"
                                                        @if ($item['quantity'] <= $item['moq']) disabled @endif
                                                        title="Decrease by 1"
                                                    >
                                                        <x-ui.icon name="remove" class="text-[16px]" />
                                                    </button>

                                                    <input
                                                        type="number"
                                                        min="{{ $item['moq'] }}"
                                                        value="{{ $item['quantity'] }}"
                                                        wire:change="updateItemQuantity({{ $pid }}, $event.target.value)"
                                                        class="w-14 h-8 text-center font-bold text-body-sm text-on-surface bg-surface-container-lowest border border-outline-variant/40 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                        title="Minimum order: {{ $item['moq'] }} {{ $item['moq_unit'] }}"
                                                    />

                                                    <button
                                                        type="button"
                                                        wire:click="increment({{ $pid }}, 1)"
                                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer"
                                                        title="Increase by 1"
                                                    >
                                                        <x-ui.icon name="add" class="text-[16px]" />
                                                    </button>
                                                    <span class="text-[11px] font-medium text-on-surface-variant">{{ $item['moq_unit'] }}</span>
                                                </div>

                                                {{-- Price & Subtotal --}}
                                                <div class="text-right">
                                                    <div class="text-xs text-on-surface-variant">@ {{ $item['unit_price_formatted'] }}</div>
                                                    <div class="text-label-md font-bold text-secondary">{{ $item['total_price_formatted'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        {{-- CENTRAL CHECKOUT VIEW --}}
                        <div class="space-y-4">
                            <button
                                type="button"
                                wire:click="backToBasket"
                                class="inline-flex items-center gap-1.5 text-label-md font-semibold text-primary hover:underline"
                            >
                                <x-ui.icon name="arrow_back" class="text-[18px]" />
                                <span>Back to Review Basket ({{ count($items) }} items)</span>
                            </button>

                            <div class="rounded-xl bg-surface-container-low p-3.5 space-y-1.5 text-body-sm">
                                <span class="text-label-sm font-bold text-on-surface uppercase tracking-wider block">Central Wholesale Order</span>
                                <div class="flex justify-between text-on-surface-variant">
                                    <span>Selected Styles:</span>
                                    <span class="font-semibold text-on-surface">{{ count($items) }} products</span>
                                </div>
                                <div class="flex justify-between text-on-surface-variant">
                                    <span>Total Wholesale Units:</span>
                                    <span class="font-semibold text-on-surface">{{ $totalUnits }} pcs</span>
                                </div>
                                <div class="flex justify-between text-on-surface-variant">
                                    <span>Combined Subtotal:</span>
                                    <span class="font-bold text-primary">{{ $subtotalFormatted }}</span>
                                </div>
                            </div>

                            <form wire:submit="checkout" id="basketCheckoutForm" class="space-y-3" novalidate>
                                <input type="text" wire:model="website" class="hidden" tabindex="-1" autocomplete="off" />

                                <div>
                                    <label class="block text-label-md font-semibold text-on-surface mb-1">
                                        Full Name / Boutique <span class="text-error">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="name"
                                        class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                        placeholder="e.g. Nkechi Okafor"
                                        required
                                    />
                                    @error('name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-label-md font-semibold text-on-surface mb-1">
                                            Email Address <span class="text-error">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            wire:model="email"
                                            class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                            placeholder="receipt@example.com"
                                            required
                                        />
                                        @error('email') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-label-md font-semibold text-on-surface mb-1">
                                            Phone / WhatsApp <span class="text-error">*</span>
                                        </label>
                                        <input
                                            type="tel"
                                            wire:model="phone"
                                            class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                            placeholder="+234 800 000 0000"
                                            required
                                        />
                                        @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-label-md font-semibold text-on-surface mb-1">
                                            Country <span class="text-error">*</span>
                                        </label>
                                        <select
                                            wire:model="country"
                                            class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary cursor-pointer"
                                            required
                                        >
                                            @foreach ($countries as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                            @endforeach
                                        </select>
                                        @error('country') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-label-md font-semibold text-on-surface mb-1">
                                            Delivery City / Park <span class="text-error">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="destination_city"
                                            class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                            placeholder="e.g. Lagos Trade Fair Park"
                                            required
                                        />
                                        @error('destination_city') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-label-md font-semibold text-on-surface mb-1">
                                        Street Address / Shop No. (Optional)
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="shipping_address"
                                        class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                        placeholder="Suite or line number"
                                    />
                                    @error('shipping_address') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-label-md font-semibold text-on-surface mb-1">
                                        Size / Color Instructions Across Basket Items (Optional)
                                    </label>
                                    <textarea
                                        wire:model="notes"
                                        rows="2"
                                        class="w-full rounded-lg border-0 bg-surface-container-low px-3.5 py-2 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                        placeholder="e.g. For shoes: Size 42 & 43. For kaftans: Navy & White mix."
                                    ></textarea>
                                    @error('notes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Drawer Footer with Sticky Totals & Actions --}}
                @if (! empty($items))
                    <div class="border-t border-outline-variant/30 p-4 sm:p-5 bg-surface-container-low shrink-0 space-y-3.5">
                        <div class="space-y-1.5 text-body-sm">
                            <div class="flex items-center justify-between text-on-surface-variant">
                                <span>Total Wholesale Quantity:</span>
                                <span class="font-bold text-on-surface">{{ $totalUnits }} pcs ({{ count($items) }} {{ count($items) === 1 ? 'style' : 'styles' }})</span>
                            </div>
                            <div class="flex items-center justify-between text-on-surface-variant">
                                <span>Ariaria Hub Quality Inspection:</span>
                                <span class="font-semibold text-secondary">FREE (100% Pre-Shipment Check)</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-1.5 border-t border-outline-variant/20">
                                <span class="text-label-md font-bold text-on-surface">Basket Total:</span>
                                <span class="text-headline-md font-extrabold text-secondary">{{ $subtotalFormatted }}</span>
                            </div>
                        </div>

                        @if ($step === 'basket')
                            <div class="space-y-2">
                                <button
                                    type="button"
                                    wire:click="proceedToCheckout"
                                    class="group w-full flex min-h-[52px] items-center justify-between gap-3 rounded-xl bg-secondary px-5 py-3 text-label-lg font-bold text-on-secondary shadow-md hover:shadow-lg hover:bg-secondary/90 transition-all active:scale-[0.99] cursor-pointer"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-on-secondary/15">
                                            <x-ui.icon name="shopping_cart_checkout" class="text-[20px]" />
                                        </div>
                                        <span>Proceed to Wholesale Checkout</span>
                                    </div>
                                    <x-ui.icon name="arrow_forward" class="text-[20px] transition-transform group-hover:translate-x-1" />
                                </button>

                                <button
                                    type="button"
                                    wire:click="close"
                                    class="w-full py-1 text-center text-label-sm font-semibold text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer"
                                >
                                    Continue Adding Wholesale Styles
                                </button>
                            </div>
                        @else
                            <div class="space-y-2">
                                <button
                                    type="submit"
                                    form="basketCheckoutForm"
                                    wire:loading.attr="disabled"
                                    class="w-full flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-label-lg font-bold text-on-secondary shadow-md hover:shadow-lg hover:bg-secondary/90 transition-all active:scale-[0.99] disabled:opacity-60 cursor-pointer"
                                >
                                    <span wire:loading.remove wire:target="checkout" class="flex items-center gap-2">
                                        <x-ui.icon name="lock" class="text-[19px]" />
                                        <span>Pay {{ $subtotalFormatted }} with Paystack</span>
                                    </span>
                                    <span wire:loading wire:target="checkout" class="inline-flex items-center gap-2">
                                        <svg class="h-5 w-5 animate-spin text-on-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Connecting to Paystack...</span>
                                    </span>
                                </button>

                                <div class="flex items-center justify-center gap-1 text-[11px] text-on-surface-variant">
                                    <x-ui.icon name="verified_user" class="text-[14px] text-secondary" />
                                    <span>Secured by <strong>Paystack</strong> &bull; Cards, Bank Transfer, USSD</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

