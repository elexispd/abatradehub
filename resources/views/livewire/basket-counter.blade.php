<div>
    <button
        type="button"
        wire:click="openBasket"
        class="relative inline-flex min-h-11 items-center gap-1.5 rounded-lg px-3 py-2 text-label-md font-bold transition-all {{ $count > 0 ? 'bg-secondary text-on-secondary shadow-xs hover:bg-secondary/90' : 'bg-surface-container text-on-surface hover:bg-surface-container-high' }}"
        title="View Wholesale Basket"
        aria-label="View Wholesale Basket with {{ $count }} items"
    >
        <x-ui.icon name="shopping_basket" class="text-[20px]" />
        <span class="hidden sm:inline">Basket</span>

        @if ($count > 0)
            <span class="ml-0.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-secondary-container px-1.5 text-[11px] font-extrabold text-on-secondary-container">
                {{ $count }}
            </span>
        @endif
    </button>
</div>

