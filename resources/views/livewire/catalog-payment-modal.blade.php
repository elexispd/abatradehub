<div>
    @if ($isOpen)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-on-surface/50 backdrop-blur-xs transition-opacity"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modalPaymentTitle"
        >
            {{-- Backdrop click --}}
            <div class="fixed inset-0" wire:click="close"></div>

            <div class="relative w-full max-w-xl rounded-2xl bg-surface-container-lowest p-5 sm:p-7 shadow-2xl transition-all z-10 max-h-[92vh] overflow-y-auto">
                <button
                    type="button"
                    wire:click="close"
                    class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors"
                    aria-label="Close modal"
                >
                    <x-ui.icon name="close" class="text-[22px]" />
                </button>

                <div class="space-y-4">
                    {{-- Header --}}
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-label-sm font-bold uppercase tracking-wider">
                            <x-ui.icon name="verified_user" class="text-[15px]" />
                            <span>Instant Wholesale Checkout</span>
                        </div>
                        <h3 class="text-headline-sm sm:text-headline-md font-bold text-on-surface mt-1" id="modalPaymentTitle">
                            {{ $productName }}
                        </h3>
                        <p class="text-body-sm text-on-surface-variant">
                            SKU: <strong class="text-on-surface">{{ $productSku }}</strong>
                            <span class="mx-1.5">&bull;</span>
                            MOQ: <strong class="text-on-surface">{{ $productMoq }} {{ $productMoqUnit }}</strong>
                        </p>
                    </div>

                    {{-- Product & Quantity Summary Box --}}
                    <div class="rounded-xl bg-surface-container-low p-3.5 sm:p-4 space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <span class="text-label-md font-semibold text-on-surface">Order Quantity:</span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="decrementQuantity(5)"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors disabled:opacity-50"
                                    @if ($quantity <= $productMoq) disabled @endif
                                    title="Decrease by 5"
                                >
                                    <x-ui.icon name="remove" class="text-[18px]" />
                                </button>

                                <input
                                    type="number"
                                    wire:model.live.debounce.300ms="quantity"
                                    min="{{ $productMoq }}"
                                    class="w-20 min-h-9 rounded-lg border-0 bg-surface-container-lowest text-center font-bold text-on-surface focus:ring-2 focus:ring-primary text-body-md"
                                />

                                <button
                                    type="button"
                                    wire:click="incrementQuantity(5)"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors"
                                    title="Increase by 5"
                                >
                                    <x-ui.icon name="add" class="text-[18px]" />
                                </button>
                                <span class="text-body-sm text-on-surface-variant">{{ $productMoqUnit }}</span>
                            </div>
                        </div>

                        {{-- Price Calculation Row --}}
                        <div class="flex items-center justify-between border-t border-outline-variant/30 pt-2.5 text-body-sm">
                            <span class="text-on-surface-variant">Wholesale Rate:</span>
                            <span class="font-bold text-primary text-label-lg">
                                {{ $this->unitPriceFormatted }} <span class="text-xs font-normal text-on-surface-variant">/ pc</span>
                            </span>
                        </div>

                        {{-- Tiers helper hint --}}
                        @if (! empty($priceTiers))
                            <div class="text-[11px] text-on-surface-variant flex flex-wrap gap-1.5 pt-1">
                                <span class="font-semibold text-secondary">Volume Tiers:</span>
                                @foreach ($priceTiers as $tier)
                                    <span class="rounded bg-surface-container px-1.5 py-0.5 {{ ($quantity >= ($tier['min_quantity'] ?? 0) && (!isset($tier['max_quantity']) || $tier['max_quantity'] === null || $quantity <= $tier['max_quantity'])) ? 'font-bold text-primary ring-1 ring-primary/40' : '' }}">
                                        {{ $tier['range'] }} pcs: {{ $tier['price_formatted'] }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($errorMessage)
                        <div class="rounded-xl bg-error-container p-3 text-body-sm text-on-error-container flex items-start gap-2">
                            <x-ui.icon name="error" class="text-[20px] shrink-0 text-error" />
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif

                    <form wire:submit="pay" class="space-y-3.5" novalidate>
                        {{-- Honeypot field --}}
                        <input type="text" wire:model="website" class="hidden" tabindex="-1" autocomplete="off" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="payment-buyer-name" class="block text-label-md font-semibold text-on-surface mb-1">
                                    Full Name / Boutique <span class="text-error">*</span>
                                </label>
                                <input
                                    id="payment-buyer-name"
                                    type="text"
                                    wire:model="name"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="e.g. Nkechi Okafor"
                                    required
                                />
                                @error('name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="payment-buyer-email" class="block text-label-md font-semibold text-on-surface mb-1">
                                    Email Address (for receipt) <span class="text-error">*</span>
                                </label>
                                <input
                                    id="payment-buyer-email"
                                    type="email"
                                    wire:model="email"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="your-email@example.com"
                                    required
                                />
                                @error('email') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="payment-buyer-phone" class="block text-label-md font-semibold text-on-surface mb-1">
                                    WhatsApp / Phone <span class="text-error">*</span>
                                </label>
                                <input
                                    id="payment-buyer-phone"
                                    type="tel"
                                    wire:model="phone"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="+234 800 000 0000"
                                    required
                                />
                                @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="payment-buyer-country" class="block text-label-md font-semibold text-on-surface mb-1">
                                    Destination Country <span class="text-error">*</span>
                                </label>
                                <select
                                    id="payment-buyer-country"
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
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="payment-buyer-city" class="block text-label-md font-semibold text-on-surface mb-1">
                                    Delivery City / Transport Park <span class="text-error">*</span>
                                </label>
                                <input
                                    id="payment-buyer-city"
                                    type="text"
                                    wire:model="destination_city"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="e.g. Lagos (Trade Fair Park), Abuja (Utako)"
                                    required
                                />
                                @error('destination_city') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="payment-buyer-address" class="block text-label-md font-semibold text-on-surface mb-1">
                                    Street Address / Shop No. (Optional)
                                </label>
                                <input
                                    id="payment-buyer-address"
                                    type="text"
                                    wire:model="shipping_address"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="e.g. Suite 14, Main Line Plaza"
                                />
                                @error('shipping_address') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="payment-buyer-specs" class="block text-label-md font-semibold text-on-surface mb-1">
                                Size / Color Breakdown or Instructions (Optional)
                            </label>
                            <textarea
                                id="payment-buyer-specs"
                                wire:model="specifications"
                                rows="2"
                                class="w-full rounded-lg border-0 bg-surface-container-low px-3.5 py-2 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                placeholder="e.g. Size 42 (10 pcs), Size 43 (10 pcs). All Black colorway."
                            ></textarea>
                            @error('specifications') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Total Settlement Box --}}
                        <div class="rounded-xl border border-primary/20 bg-primary/5 p-4 space-y-2">
                            <div class="flex items-center justify-between text-body-sm text-on-surface-variant">
                                <span>Total Units:</span>
                                <span class="font-semibold text-on-surface">{{ $quantity }} {{ $productMoqUnit }}</span>
                            </div>
                            <div class="flex items-center justify-between text-body-sm text-on-surface-variant">
                                <span>Quality Inspection:</span>
                                <span class="font-semibold text-secondary">FREE (100% Checked)</span>
                            </div>
                            <div class="flex items-baseline justify-between border-t border-primary/20 pt-2">
                                <span class="text-label-lg font-bold text-on-surface">Total Amount to Pay:</span>
                                <span class="text-headline-md font-bold text-primary">{{ $this->subtotalFormatted }}</span>
                            </div>
                        </div>

                        {{-- Paystack Trust Badges --}}
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant px-1">
                            <span class="inline-flex items-center gap-1">
                                <x-ui.icon name="lock" class="text-[14px] text-secondary" />
                                <span>Secured by <strong>Paystack</strong></span>
                            </span>
                            <span>Cards &bull; Bank Transfer &bull; USSD</span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="flex-1 flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-label-lg font-bold text-on-primary shadow-md hover:bg-primary/90 transition-all active:scale-[0.99] disabled:opacity-60 cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="pay" class="flex items-center gap-2">
                                    <x-ui.icon name="shopping_cart_checkout" class="text-[20px]" />
                                    <span>Pay {{ $this->subtotalFormatted }} with Paystack</span>
                                </span>
                                <span wire:loading wire:target="pay" class="inline-flex items-center gap-2">
                                    <svg class="h-5 w-5 animate-spin text-on-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Connecting to Paystack...</span>
                                </span>
                            </button>

                            <button
                                type="button"
                                wire:click="close"
                                class="min-h-12 px-4 py-3 rounded-xl bg-surface-container text-on-surface font-semibold text-label-md hover:bg-surface-container-high transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

