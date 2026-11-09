<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;

class BasketService
{
    protected const SESSION_KEY = 'wholesale_basket';

    /**
     * Retrieve all items currently in the wholesale basket.
     *
     * @return array<int, array{
     *     product_id: int,
     *     name: string,
     *     sku: string,
     *     slug: string,
     *     category_slug: ?string,
     *     url: string,
     *     primary_image: ?string,
     *     moq: int,
     *     moq_unit: string,
     *     unit_price: float,
     *     unit_price_formatted: string,
     *     quantity: int,
     *     total_price: float,
     *     total_price_formatted: string,
     *     specifications: ?string
     * }>
     */
    public function all(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    /**
     * Check if a specific product is in the basket.
     */
    public function has(int $productId): bool
    {
        $items = $this->all();
        return isset($items[$productId]);
    }

    /**
     * Get a specific product entry from the basket.
     */
    public function get(int $productId): ?array
    {
        $items = $this->all();
        return $items[$productId] ?? null;
    }

    /**
     * Add or increment a product in the wholesale basket.
     */
    public function add(Product $product, ?int $quantity = null, ?string $specifications = null): array
    {
        $items = $this->all();
        $id = $product->id;
        $product->loadMissing('priceTiers', 'media', 'category');

        $minMoq = max(1, (int) $product->moq);
        $qtyToAdd = $quantity !== null ? max(1, $quantity) : $minMoq;

        if (isset($items[$id])) {
            $newQuantity = $items[$id]['quantity'] + $qtyToAdd;
            $newSpecs = $specifications ?: $items[$id]['specifications'];
        } else {
            $newQuantity = max($minMoq, $qtyToAdd);
            $newSpecs = $specifications;
        }

        $unitPrice = $product->getUnitPriceForQuantity($newQuantity);
        $totalPrice = round($newQuantity * $unitPrice, 2);

        $primaryMedia = $product->getFirstMedia('images');
        $imageUrl = $primaryMedia
            ? ($primaryMedia->hasGeneratedConversion('card') ? $primaryMedia->getUrl('card') : $primaryMedia->getUrl())
            : null;

        $items[$id] = [
            'product_id' => $id,
            'name' => $product->name,
            'sku' => $product->sku ?? '',
            'slug' => $product->slug,
            'category_slug' => $product->category?->slug,
            'url' => $product->url,
            'primary_image' => $imageUrl,
            'moq' => $minMoq,
            'moq_unit' => $product->moq_unit ?: 'pcs',
            'unit_price' => $unitPrice,
            'unit_price_formatted' => Money::format($unitPrice, $product->currency ?: 'NGN'),
            'quantity' => $newQuantity,
            'total_price' => $totalPrice,
            'total_price_formatted' => Money::format($totalPrice, $product->currency ?: 'NGN'),
            'specifications' => $newSpecs,
        ];

        session()->put(self::SESSION_KEY, $items);

        return $items[$id];
    }

    /**
     * Update quantity for a product in the basket.
     */
    public function updateQuantity(int $productId, int $quantity): ?array
    {
        $items = $this->all();

        if (! isset($items[$productId])) {
            return null;
        }

        $product = Product::with('priceTiers')->find($productId);
        if (! $product) {
            $this->remove($productId);
            return null;
        }

        $minMoq = max(1, (int) $product->moq);
        $newQty = max($minMoq, $quantity);

        $unitPrice = $product->getUnitPriceForQuantity($newQty);
        $totalPrice = round($newQty * $unitPrice, 2);

        $items[$productId]['quantity'] = $newQty;
        $items[$productId]['unit_price'] = $unitPrice;
        $items[$productId]['unit_price_formatted'] = Money::format($unitPrice, $product->currency ?: 'NGN');
        $items[$productId]['total_price'] = $totalPrice;
        $items[$productId]['total_price_formatted'] = Money::format($totalPrice, $product->currency ?: 'NGN');

        session()->put(self::SESSION_KEY, $items);

        return $items[$productId];
    }

    /**
     * Update size / note specifications for an item.
     */
    public function updateSpecifications(int $productId, ?string $specs): void
    {
        $items = $this->all();

        if (isset($items[$productId])) {
            $items[$productId]['specifications'] = $specs ?: null;
            session()->put(self::SESSION_KEY, $items);
        }
    }

    /**
     * Remove an item from the basket.
     */
    public function remove(int $productId): array
    {
        $items = $this->all();
        unset($items[$productId]);
        session()->put(self::SESSION_KEY, $items);

        return $items;
    }

    /**
     * Empty the entire basket.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Count of distinct wholesale items in basket.
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Sum of total units (pieces/pairs) across all items.
     */
    public function totalUnits(): int
    {
        $total = 0;
        foreach ($this->all() as $item) {
            $total += (int) ($item['quantity'] ?? 0);
        }
        return $total;
    }

    /**
     * Calculate gross subtotal of all items in the basket.
     */
    public function subtotal(): float
    {
        $sum = 0.0;
        foreach ($this->all() as $item) {
            $sum += (float) ($item['total_price'] ?? 0);
        }
        return round($sum, 2);
    }

    public function subtotalFormatted(): string
    {
        return Money::format($this->subtotal(), 'NGN');
    }
}

