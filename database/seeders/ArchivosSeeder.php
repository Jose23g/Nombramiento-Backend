<?php

namespace Database\Seeders;

use App\Models\Archivos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArchivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Archivos::factory()->count(10)->create();
    }
}
