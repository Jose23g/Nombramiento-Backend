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
        Schema::create('grupo_aprobados', function (Blueprint $table) {
            $table->id();
            $table->string('grupo');
            $table->string('cupo');
            $table->unsignedBigInteger('id_detalle');
            $table->unsignedBigInteger('id_profesor');
            $table->unsignedBigInteger('id_horario');
            $table->foreign('id_detalle')->references('id')->on('detalle_aprobacion_cursos');
            $table->foreign('id_profesor')->references('id')->on('usuarios');
            $table->foreign('id_horario')->references('id')->on('horarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_aprobados');
    }
};
