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
        Schema::create('detalle_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_curso_id');
            $table->unsignedBigInteger('curso_id');
            $table->string('grupos');
            $table->string('horas_teoricas');
            $table->string('horas_practicas');
            $table->string('horas_laboratorio');
            $table->foreign('solicitud_curso_id')->references('id')->on('solicitud_cursos');
            $table->foreign('curso_id')->references('id')->on('cursos');
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
