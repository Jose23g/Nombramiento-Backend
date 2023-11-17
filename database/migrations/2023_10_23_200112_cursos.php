<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('sigla');
            $table->string('nombre');
            $table->string('creditos');
            $table->string('grado_anual');
            $table->string('ciclo');
            $table->integer('horas_teoricas')->nullable()->default(0);
            $table->integer('horas_practicas')->nullable()->default(0);
            $table->integer('horas_laboratorio')->nullable()->default(0);
            $table->string('individual_colegiado')->nullable();
            $table->string('tutoria')->nullable();
            $table->string('horas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
