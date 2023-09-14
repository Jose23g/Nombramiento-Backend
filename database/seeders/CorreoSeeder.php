<?php

namespace Database\Seeders;

use App\Models\Correo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CorreoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Correo::factory()->count(10)->create();
    }
}
