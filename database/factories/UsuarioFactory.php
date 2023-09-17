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
            'contrasena' => bcrypt('password'),
            'imagen' => base64_encode(file_get_contents('archivo.pdf')),
            'id_rol' => \App\Models\Rol::factory()->create()->id,
            'id_persona' => \App\Models\Persona::factory()->create()->id,
            'correo' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }
}
