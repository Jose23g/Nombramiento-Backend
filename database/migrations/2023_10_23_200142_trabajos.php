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
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jornada_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('estado_id');
            $table->unsignedBigInteger('fecha_id')->nullable();
            $table->unsignedBigInteger('tipo_id')->nullable();
            $table->string('lugar_trabajo');
            $table->string('cargo');
            $table->foreign('fecha_id')->references('id')->on('fechas');
            $table->foreign('tipo_id')->references('id')->on('tipo');
            $table->foreign('jornada_id')->references('id')->on('jornadas');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('estado_id')->references('id')->on('estados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajos');
    }
};
