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
        Schema::create('dias', function (Blueprint $table) {
            $table->id();
            $table->time('entrada');
            $table->time('salida');
            $table->unsignedBigInteger('id_dia');
            $table->unsignedBigInteger('id_horario');
            $table->foreign('id_dia')->references('id')->on('dia');
            $table->foreign('id_horario')->references('id')->on('horarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias');
    }
};
