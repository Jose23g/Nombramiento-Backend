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
        Schema::create('detalle_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('ciclo');
            $table->string('grupos');
            $table->string('recinto');
            $table->string('carga');
            $table->unsignedBigInteger('id_solicitud');
            $table->unsignedBigInteger('id_curso');
            $table->foreign('id_solicitud')->references('id')->on('solicitud_cursos');
            $table->foreign('id_curso')->references('id')->on('cursos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_solicitudes');
    }
};
