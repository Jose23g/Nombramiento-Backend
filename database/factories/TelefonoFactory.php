<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Telefono>
 */
class TelefonoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personal' => $this->faker->phoneNumber,
            'trabajo' => $this->faker->phoneNumber,
            'otro' => $this->faker->phoneNumber,
            'id_persona' => \App\Models\Persona::factory()->create()->id,
        ];
    }
}
