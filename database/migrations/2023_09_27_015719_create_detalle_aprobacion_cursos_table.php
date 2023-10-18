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
            $table->unsignedBigInteger('id_detalle');
            $table->unsignedBigInteger('id_solicitud');
            $table->foreign('id_detalle')->references('id')->on('detalle_solicitudes');
            $table->foreign('id_solicitud')->references('id')->on('aprobacion_solicitud_cursos');
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
