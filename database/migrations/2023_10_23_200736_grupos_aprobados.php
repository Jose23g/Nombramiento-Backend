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
        Schema::create('grupos_aprobados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_grupo_id');
            $table->unsignedBigInteger('detalle_aprobado_id');
            $table->foreign('solicitud_grupo_id')->references('id')->on('solicitud_grupos');
            $table->foreign('detalle_aprobado_id')->references('id')->on('detalle_aprobacion_cursos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos_aprobados');
    }
};
