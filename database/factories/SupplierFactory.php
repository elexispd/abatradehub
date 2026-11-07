<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Supplier> */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'contact_person' => fake()->name(),
            'phone' => '080'.fake()->numerify('########'),
            'whatsapp' => '234803'.fake()->numerify('#######'),
            'email' => fake()->unique()->safeEmail(),
            'market_location' => fake()->randomElement([
                'Ariaria International Market, Aba',
                'Ngwa Road, Aba',
                'Cemetery Market, Aba',
                'Eziukwu Market, Aba',
                'Powerline Market, Aba',
            ]),
            'address' => fake()->streetAddress().', Aba, Abia State',
            'notes' => null,
            'is_verified' => fake()->boolean(70),
            'is_active' => true,
        ];
    }
}
