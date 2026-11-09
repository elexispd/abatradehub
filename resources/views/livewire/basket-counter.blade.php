<div>
    <button
        type="button"
        wire:click="openBasket"
        class="relative inline-flex h-9 sm:h-10 items-center justify-center gap-1 sm:gap-1.5 rounded-lg px-2 sm:px-3 text-label-sm sm:text-label-md font-bold transition-all {{ $count > 0 ? 'bg-secondary text-on-secondary shadow-xs hover:bg-secondary/90' : 'bg-surface-container text-on-surface hover:bg-surface-container-high' }}"
        title="View Wholesale Basket"
        aria-label="View Wholesale Basket with {{ $count }} items"
    >
        <x-ui.icon name="shopping_basket" class="text-[18px] sm:text-[20px]" />
        <span class="hidden sm:inline">Basket</span>

        @if ($count > 0)
            <span class="inline-flex h-4.5 min-w-4.5 sm:h-5 sm:min-w-5 items-center justify-center rounded-full bg-secondary-container px-1 sm:px-1.5 text-[10px] sm:text-[11px] font-extrabold text-on-secondary-container">
                {{ $count }}
            </span>
        @endif
    </button>
</div>

