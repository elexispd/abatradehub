<?php

namespace Database\Seeders;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class EnquirySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::published()->get();

        foreach (EnquiryStatus::cases() as $status) {
            Enquiry::factory()->count($status === EnquiryStatus::New ? 5 : 3)->status($status)->create()
                ->each(function (Enquiry $enquiry) use ($products) {
                    foreach ($products->random(fake()->numberBetween(1, 3)) as $product) {
                        EnquiryItem::create([
                            'enquiry_id' => $enquiry->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_sku' => $product->sku,
                            'quantity' => $product->moq * fake()->numberBetween(1, 4),
                        ]);
                    }
                });
        }
    }
}
