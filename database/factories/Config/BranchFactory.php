<?php

namespace Database\Factories\Config;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),          // nombre de la sucursal
            'address' => $this->faker->address(),       // dirección
            'phone' => $this->faker->phoneNumber(),     // teléfono
        ];
    }
}
