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
        Schema::create('horarios_grupos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dia_id');
            $table->unsignedBigInteger('solicitud_grupo_id');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->foreign('dia_id')->references('id')->on('dias');
            $table->foreign('solicitud_grupo_id')->references('id')->on('solicitud_grupos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_grupos');
    }
};
