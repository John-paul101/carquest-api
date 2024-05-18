<?php

namespace Database\Factories;

use App\Models\ShippingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Silver', 'Gold', 'Diamond']),
            'price' => $this->faker->randomFloat(2, 10, 50),
            'shipping_location_id' => ShippingLocation::factory(),
        ];
    }
}
