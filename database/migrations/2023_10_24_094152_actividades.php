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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('p_seis_id');
            $table->unsignedBigInteger('carga_id');
            $table->unsignedBigInteger('estado_id');
            $table->unsignedBigInteger('categoria_id');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('tipo');
            $table->string('estudiante');
            $table->string('modalidad');
            $table->string('grado');
            $table->string('postgrado');
            $table->string('numero_oficio');
            $table->string('nombre');
            $table->string('cargo_comision');
            $table->foreign('p_seis_id')->references('id')->on('p_seis');
            $table->foreign('carga_id')->references('id')->on('cargas');
            $table->foreign('estado_id')->references('id')->on('estados');
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
