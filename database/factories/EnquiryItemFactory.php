<?php

namespace Database\Factories;

use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EnquiryItem> */
class EnquiryItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'enquiry_id' => Enquiry::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $product->moq * fake()->numberBetween(1, 5),
            'note' => null,
        ];
    }
}
