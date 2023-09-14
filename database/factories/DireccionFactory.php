<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Direccion>
 */
class DireccionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'otrassenas' => $this->faker->address,
            'id_barrio' => \App\Models\Barrio::factory()->create()->id,
            'id_persona' => \App\Models\Persona::factory()->create()->id,
        ];
    }
}
