<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitud_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coordinador_id');
            $table->unsignedBigInteger('carrera_id');
            $table->unsignedBigInteger('estado_id');
            $table->unsignedBigInteger('fecha_solicitud_id');
            $table->string('observacion')->nullable();
            $table->string('carga_total')->nullable();
            $table->foreign('coordinador_id')->references('id')->on('usuarios');
            $table->foreign('carrera_id')->references('id')->on('carreras');
            $table->foreign('estado_id')->references('id')->on('estados');
            $table->foreign('fecha_solicitud_id')->references('id')->on('fecha_solicitudes');
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
