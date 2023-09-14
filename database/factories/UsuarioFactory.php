<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario' => $this->faker->userName,
            'contrasena' => bcrypt('password123'),
            // Puedes personalizar la contraseña aquí.
            'id_rol' => \App\Models\Rol::factory()->create()->id,
            'id_persona' => \App\Models\Persona::factory()->create()->id,
        ];
    }
}
