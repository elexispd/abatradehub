<?php

namespace Database\Factories;

use App\Models\PriceTier;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PriceTier> */
class PriceTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'min_quantity' => 10,
            'max_quantity' => 49,
            'unit_price' => fake()->numberBetween(30, 400) * 100,
        ];
    }
}
