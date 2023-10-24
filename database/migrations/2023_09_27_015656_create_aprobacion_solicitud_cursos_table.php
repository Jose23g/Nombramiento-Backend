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
        Schema::create('aprobacion_solicitud_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitud');
            $table->unsignedBigInteger('id_encargado');
            $table->foreign('id_solicitud')->references('id')->on('solicitud_cursos');
            $table->foreign('id_encargado')->references('id')->on('usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprobacion_solicitud_cursos');
    }
};
