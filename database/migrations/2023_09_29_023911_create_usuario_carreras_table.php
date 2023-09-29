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
        Schema::create('usuario_carreras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_coordinador');
            $table->unsignedBigInteger('id_carrera');
            $table->foreign('id_coordinador')->references('id')->on('usuarios');
            $table->foreign('id_carrera')->references('id')->on('carreras');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_carreras');
    }
};
