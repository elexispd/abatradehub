<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['Ngozi Linen Works', 'Ariaria International Market, Aba'],
            ['Okafor Senator Collections', 'Cemetery Market, Aba'],
            ['Eze Footwear Industries', 'Ngwa Road, Aba'],
            ['Amaka Leather Hub', 'Eziukwu Market, Aba'],
            ['Chinedu Fabrics & Textiles', 'Ariaria International Market, Aba'],
        ];

        foreach ($suppliers as [$name, $market]) {
            Supplier::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'market_location' => $market,
                'is_verified' => true,
            ]);
        }
    }
}
