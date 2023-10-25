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
        Schema::create('detalle_aprobacion_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_aprobado_id');
            $table->unsignedBigInteger('detalle_solicitud_id');
            $table->foreign('curso_aprobado_id')->references('id')->on('aprobacion_solicitud_cursos');
            $table->foreign('detalle_solicitud_id')->references('id')->on('detalle_aprobacion_cursos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_aprobacion_cursos');
    }
};
