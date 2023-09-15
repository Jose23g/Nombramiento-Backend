<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barrio>
 */
class BarrioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->city,
            'id_distrito' => \App\Models\Distrito::factory()->create()->id,
            'id_canton' => \App\Models\Canton::factory()->create()->id,
            'id_provincia' => \App\Models\Provincia::factory()->create()->id,
        ];
    }
}
