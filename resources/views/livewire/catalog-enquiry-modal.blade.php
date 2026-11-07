<div>
    @if ($isOpen)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/40 backdrop-blur-xs transition-opacity"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modalProductName"
        >
            {{-- Backdrop click --}}
            <div class="fixed inset-0" wire:click="close"></div>

            <div class="relative w-full max-w-lg rounded-2xl bg-surface-container-lowest p-6 sm:p-8 shadow-2xl transition-all z-10 max-h-[90vh] overflow-y-auto">
                <button
                    type="button"
                    wire:click="close"
                    class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors"
                    aria-label="Close modal"
                >
                    <x-ui.icon name="close" class="text-[22px]" />
                </button>

                @if ($submitted)
                    <div class="py-4 text-center space-y-4">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-secondary-fixed text-secondary">
                            <x-ui.icon name="check_circle" class="text-[32px]" />
                        </div>
                        <h3 class="text-headline-md font-bold text-on-surface">Enquiry Submitted!</h3>
                        <p class="text-body-md text-on-surface-variant max-w-md mx-auto">
                            Thank you, <strong class="text-on-surface">{{ $name }}</strong>. Our Aba sourcing desk has received your request for <strong class="text-on-surface">{{ $quantity }} pcs of {{ $productName }}</strong>.
                        </p>

                        <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
                            
                            <button
                                type="button"
                                wire:click="close"
                                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-surface-container px-5 py-2.5 text-label-lg font-semibold text-on-surface hover:bg-surface-container-high transition-colors"
                            >
                                Done
                            </button>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <span class="text-label-sm font-bold uppercase tracking-wider text-primary">Fast Wholesale Enquiry</span>
                            <h3 class="text-headline-sm sm:text-headline-md font-bold text-on-surface mt-1" id="modalProductName">
                                {{ $productName }}
                            </h3>
                            <p class="text-body-sm text-on-surface-variant mt-0.5">
                                Rate: <strong class="text-on-surface">{{ $productPrice }}</strong> | <span>MOQ: {{ $productMoq }} pieces</span>
                            </p>
                        </div>

                        <form wire:submit="submit" class="space-y-3.5 pt-1" novalidate>
                            <div>
                                <label for="modal-buyer-name" class="block text-label-md font-semibold text-on-surface mb-1">Your Name / Boutique Name</label>
                                <input
                                    id="modal-buyer-name"
                                    type="text"
                                    wire:model="name"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="e.g. Chisom Fashion Hub"
                                    required
                                />
                                @error('name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="modal-buyer-phone" class="block text-label-md font-semibold text-on-surface mb-1">WhatsApp Phone Number</label>
                                <input
                                    id="modal-buyer-phone"
                                    type="tel"
                                    wire:model="phone"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                    placeholder="+234 800 000 0000"
                                    required
                                />
                                @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="modal-buyer-qty" class="block text-label-md font-semibold text-on-surface mb-1">Quantity Needed</label>
                                    <input
                                        id="modal-buyer-qty"
                                        type="number"
                                        min="{{ $productMoq }}"
                                        wire:model="quantity"
                                        class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                        required
                                    />
                                    @error('quantity') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="modal-buyer-destination" class="block text-label-md font-semibold text-on-surface mb-1">Destination (City/Park)</label>
                                    <input
                                        id="modal-buyer-destination"
                                        type="text"
                                        wire:model="destination"
                                        class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary"
                                        placeholder="e.g. Trade Fair, Lagos"
                                    />
                                    @error('destination') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="modal-buyer-notes" class="block text-label-md font-semibold text-on-surface mb-1">Specific notes (Colours, sizing breakdown)</label>
                                <textarea
                                    id="modal-buyer-notes"
                                    wire:model="notes"
                                    rows="2"
                                    class="w-full rounded-lg border-0 bg-surface-container-low px-3.5 py-2.5 text-body-md text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary resize-none"
                                    placeholder="e.g. 5 Medium in Navy, 5 Large in Terracotta"
                                ></textarea>
                                @error('notes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            {{-- Honeypot --}}
                            <div class="hidden" aria-hidden="true">
                                <label for="modal-buyer-website">Website</label>
                                <input id="modal-buyer-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    wire:click="close"
                                    class="min-h-11 px-4 py-2 rounded-lg bg-surface-container text-on-surface font-label-md hover:bg-surface-container-high transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="min-h-11 px-5 py-2 rounded-lg bg-primary-container text-on-primary-container font-label-md font-bold hover:bg-primary transition-colors shadow-sm disabled:opacity-70 flex items-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="submit">Submit Wholesale Enquiry</span>
                                    <span wire:loading wire:target="submit">Sending...</span>
                                    <x-ui.icon name="send" class="text-[18px]" wire:loading.remove wire:target="submit" />
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

