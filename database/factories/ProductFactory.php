<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));
        $minLead = fake()->numberBetween(2, 5);

        return [
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'sku' => 'ATH-'.strtoupper(fake()->unique()->bothify('??-####')),
            'short_description' => fake()->sentence(10),
            'description' => collect(fake()->paragraphs(3))->map(fn ($p) => "<p>{$p}</p>")->implode(''),
            'moq' => fake()->randomElement([6, 10, 12, 15, 20, 50]),
            'moq_unit' => 'pieces',
            'base_price' => fake()->numberBetween(30, 400) * 100,
            'currency' => 'NGN',
            'lead_time_min_days' => $minLead,
            'lead_time_max_days' => $minLead + fake()->numberBetween(0, 3),
            'stock_quantity' => fake()->boolean(80) ? fake()->numberBetween(0, 500) : null,
            'low_stock_threshold' => 20,
            'status' => ProductStatus::Published,
            'is_featured' => fake()->boolean(15),
            'published_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Draft, 'published_at' => null]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => fake()->numberBetween(0, 15), 'low_stock_threshold' => 20]);
    }
}
