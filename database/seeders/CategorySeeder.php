<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Linen & Co-ords', 'checkroom', 'Relaxed linen sets, shirts and co-ords made in Aba.'],
            ['Men\'s Wear', 'man', 'Senator wear, shirts, trousers and native outfits for men.'],
            ['Women\'s Wear', 'woman', 'Dresses, tops and everyday fashion for women.'],
            ['Footwear', 'steps', 'Sandals, slippers, loafers and sneakers from Aba shoemakers.'],
            ['Bags & Leather Goods', 'shopping_bag', 'Handbags, backpacks, belts and wallets in leather and synthetics.'],
            ['Fabrics & Textiles', 'texture', 'Ankara, lace, linen and plain fabrics by the bundle.'],
        ];

        foreach ($categories as $i => [$name, $icon, $description]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'description' => $description, 'sort_order' => $i, 'is_active' => true],
            );
        }
    }
}
