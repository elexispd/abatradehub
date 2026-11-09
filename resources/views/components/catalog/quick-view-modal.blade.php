<div
    x-data="{
        isOpen: false,
        product: null,
        activeImage: '',
        open(data) {
            this.product = data;
            this.activeImage = (data.images && data.images.length > 0) ? data.images[0].card : (data.primary_image || '');
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        openEnquiry() {
            const prod = this.product;
            this.close();
            this.$dispatch('open-enquiry-modal', { product: prod });
        },
        addToBasket() {
            const prod = this.product;
            this.close();
            this.$dispatch('add-to-basket', { product: prod });
        }
    }"
    x-on:open-quick-view.window="open($event.detail)"
    x-on:keydown.escape.window="close()"
    x-cloak
>
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-on-surface/50 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0" x-on:click="close()"></div>

        {{-- Modal Box --}}
        <div
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-3xl rounded-2xl bg-surface-container-lowest p-4 sm:p-6 shadow-2xl z-10 max-h-[92vh] overflow-y-auto"
        >
            <button
                type="button"
                x-on:click="close()"
                class="absolute top-3.5 right-3.5 sm:top-4 sm:right-4 z-20 flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container-lowest/80 text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors"
                aria-label="Close modal"
            >
                <x-ui.icon name="close" class="text-[22px]" />
            </button>

            <template x-if="product">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    {{-- Image Gallery column --}}
                    <div class="space-y-3">
                        <div class="aspect-[4/5] w-full overflow-hidden rounded-xl bg-surface-container-low relative">
                            <img
                                x-bind:src="activeImage"
                                x-bind:alt="product.name"
                                class="h-full w-full object-cover object-center"
                            />
                            <div class="absolute top-3 left-3">
                                <span class="rounded-full bg-surface-container-lowest/90 px-2.5 py-1 text-label-sm font-semibold text-on-surface shadow-xs backdrop-blur-sm" x-text="product.category_name"></span>
                            </div>
                        </div>

                        {{-- Thumbnails strip if multiple --}}
                        <template x-if="product.images && product.images.length > 1">
                            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                                <template x-for="(img, idx) in product.images" x-bind:key="idx">
                                    <button
                                        type="button"
                                        x-on:click="activeImage = img.card"
                                        class="h-14 w-14 shrink-0 rounded-lg overflow-hidden border-2 transition-all"
                                        x-bind:class="activeImage === img.card ? 'border-primary' : 'border-transparent opacity-70 hover:opacity-100'"
                                    >
                                        <img x-bind:src="img.thumb" class="h-full w-full object-cover" />
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Product Info column --}}
                    <div class="flex flex-col space-y-4">
                        <div>
                            <div class="flex items-center gap-2 text-label-sm text-secondary font-semibold">
                                <x-ui.icon name="verified" class="text-[16px]" />
                                <span x-text="product.market_location"></span>
                            </div>

                            <h2 class="text-headline-md font-bold text-on-surface mt-1" x-text="product.name"></h2>

                            <div class="flex items-center gap-2 mt-1 text-label-sm text-on-surface-variant">
                                <span>SKU: <strong class="text-on-surface" x-text="product.sku"></strong></span>
                                <span>&bull;</span>
                                <span x-text="product.lead_time ? 'Lead time: ' + product.lead_time : ''"></span>
                            </div>
                        </div>

                        {{-- Price & Tiers --}}
                        <div class="rounded-xl bg-surface-container-low p-4 space-y-2">
                            <div class="flex items-baseline justify-between">
                                <span class="text-label-md text-on-surface-variant">Wholesale Rate:</span>
                                <span class="text-headline-md font-bold text-primary">
                                    <span x-text="product.starting_price_formatted"></span>
                                    <span class="text-body-sm font-normal text-on-surface-variant">/ piece</span>
                                </span>
                            </div>

                            {{-- Tiers table if any --}}
                            <template x-if="product.price_tiers && product.price_tiers.length > 0">
                                <div class="pt-2 border-t border-outline-variant/30 space-y-1">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Bulk Price Tiers</p>
                                    <div class="grid grid-cols-2 gap-2 text-body-sm">
                                        <template x-for="tier in product.price_tiers" x-bind:key="tier.range">
                                            <div class="flex justify-between bg-surface-container-lowest px-2.5 py-1 rounded">
                                                <span class="text-on-surface-variant" x-text="tier.range + ' pcs:'"></span>
                                                <span class="font-bold text-secondary" x-text="tier.price_formatted"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Specs summary --}}
                        <div class="grid grid-cols-2 gap-3 text-body-sm">
                            <div class="rounded-lg bg-surface-container p-2.5">
                                <span class="text-label-sm text-on-surface-variant block">Minimum Order</span>
                                <span class="text-headline-sm font-bold text-on-surface" x-text="product.moq + ' ' + product.moq_unit"></span>
                            </div>
                            <div class="rounded-lg bg-surface-container p-2.5">
                                <span class="text-label-sm text-on-surface-variant block">Inspection Guarantee</span>
                                <span class="text-label-md font-bold text-secondary block mt-1">100% Pre-Shipment Check</span>
                            </div>
                        </div>

                        {{-- Short Description --}}
                        <template x-if="product.short_description">
                            <p class="text-body-sm text-on-surface-variant leading-relaxed" x-text="product.short_description"></p>
                        </template>

                        {{-- Action Buttons --}}
                        <div class="pt-2 space-y-2.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <button
                                    type="button"
                                    x-on:click="openEnquiry()"
                                    class="flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-4 py-2.5 text-label-md sm:text-label-lg font-bold text-on-primary-container hover:bg-primary transition-colors shadow-xs"
                                >
                                    <x-ui.icon name="edit_note" class="text-[20px]" />
                                    <span>Make an Enquiry</span>
                                </button>

                                <button
                                    type="button"
                                    x-on:click="addToBasket()"
                                    class="flex min-h-11 items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-label-md sm:text-label-lg font-bold text-on-secondary hover:bg-secondary/90 transition-colors shadow-sm"
                                >
                                    <x-ui.icon name="shopping_basket" class="text-[20px]" />
                                    <span>Add to Basket</span>
                                </button>
                            </div>

                            <a
                                x-bind:href="product.url"
                                class="flex min-h-11 items-center justify-center gap-1.5 text-label-md text-primary font-semibold hover:underline"
                            >
                                <span>View Full Product Page & Specifications</span>
                                <x-ui.icon name="arrow_forward" class="text-[16px]" />
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

