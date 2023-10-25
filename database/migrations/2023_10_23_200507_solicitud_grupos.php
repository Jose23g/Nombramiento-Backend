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
        Schema::create('solicitud_grupos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profesor_id');
            $table->unsignedBigInteger('detalle_solicitud_id');
            $table->unsignedBigInteger('carga_id');
            $table->string('grupo');
            $table->string('cupo');
            $table->string('individual_colegiado');
            $table->string('tutoria');
            $table->string('horas');
            $table->string('recinto');
            $table->foreign('profesor_id')->references('id')->on('usuarios');
            $table->foreign('detalle_solicitud_id')->references('id')->on('detalle_solicitudes');
            $table->foreign('carga_id')->references('id')->on('cargas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_grupos');
    }
};
