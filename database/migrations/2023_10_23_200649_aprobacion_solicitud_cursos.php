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
        Schema::create('aprobacion_solicitud_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_curso_id');
            $table->unsignedBigInteger('encargado_id');
            $table->foreign('solicitud_curso_id')->references('id')->on('solicitud_cursos');
            $table->foreign('encargado_id')->references('id')->on('usuarios');
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
