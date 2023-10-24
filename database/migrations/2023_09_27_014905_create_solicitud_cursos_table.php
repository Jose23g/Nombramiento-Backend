<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitud_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('anio');
            $table->string('semestre');
            $table->dateTime('fecha');
            $table->unsignedBigInteger('id_coordinador');
            $table->unsignedBigInteger('id_estado');
            $table->unsignedBigInteger('id_carrera');
            $table->foreign('id_coordinador')->references('id')->on('usuarios');
            $table->foreign('id_carrera')->references('id')->on('carreras');
            $table->foreign('id_estado')->references('id')->on('estados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_cursos');
    }
};
