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
        Schema::create('grupo_aprobados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_detalle');
            $table->unsignedBigInteger('id_solicitud');
            $table->foreign('id_detalle')->references('id')->on('detalle_aprobacion_cursos');
            $table->foreign('id_solicitud')->references('id')->on('solicitud_grupos');
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
