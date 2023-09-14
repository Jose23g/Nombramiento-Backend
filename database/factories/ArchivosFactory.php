<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Archivos>
 */
class ArchivosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word . '.pdf',
            'tipo' => 'pdf',
            // Tipo de archivo (puedes personalizarlo)
            'file' => base64_encode(file_get_contents('archivo.pdf')),
            // Reemplaza 'ruta/al/archivo.pdf' con la ubicación del archivo que desees usar como ejemplo.
            'id_persona' => \App\Models\Persona::factory()->create()->id,
        ];
    }
}