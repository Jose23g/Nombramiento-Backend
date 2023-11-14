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
        Schema::create('p_seis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profesor_id');
            $table->unsignedBigInteger('solicitud_grupo_id');
            $table->unsignedBigInteger('jornada_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('cargo_categoria');
            $table->foreign('profesor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('solicitud_grupo_id')->references('id')->on('solicitud_grupos')->onDelete('cascade');
            $table->foreign('jornada_id')->references('id')->on('cargas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_seis');
    }
};
