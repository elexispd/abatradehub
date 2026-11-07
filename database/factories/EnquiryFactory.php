<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/** @extends Factory<Enquiry> */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => Enquiry::generateReference(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+234'.fake()->numerify('80########'),
            'company' => fake()->boolean(60) ? fake()->company() : null,
            'country' => Arr::random(['Nigeria', 'Ghana', 'Cameroon', 'Kenya', 'Senegal', 'Cote d\'Ivoire']),
            'city' => fake()->city(),
            'message' => fake()->sentence(15),
            'source' => Arr::random(['form', 'whatsapp']),
            'status' => EnquiryStatus::New,
        ];
    }

    public function status(EnquiryStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
            'contacted_at' => $status === EnquiryStatus::New ? null : now()->subDays(fake()->numberBetween(0, 10)),
        ]);
    }
}
