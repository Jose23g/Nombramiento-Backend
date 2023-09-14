<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Correo>
 */
class CorreoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personal' => $this->faker->email,
            'trabajo' => $this->faker->companyEmail,
            'otro' => $this->faker->unique()->safeEmail,
            'id_persona' => \App\Models\Persona::factory()->create()->id,
        ];
    }
}
