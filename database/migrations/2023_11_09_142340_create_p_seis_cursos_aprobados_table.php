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
        Schema::create('p_seis_cursos_aprobados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('p_seis_id');
            $table->unsignedBigInteger('curso_aprobado_id');
            $table->foreign('p_seis_id')->references('id')->on('p_seis')->onDelete('cascade');
            $table->foreign('curso_aprobado_id')->references('id')->on('aprobacion_solicitud_cursos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_seis_cursos_aprobados');
    }
};
