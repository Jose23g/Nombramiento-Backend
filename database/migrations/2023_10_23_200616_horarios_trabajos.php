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
        Schema::create('horarios_trabajos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dia_id');
            $table->unsignedBigInteger('trabajo_id');
            $table->string('hora_inicio');
            $table->string('hora_fin');
            $table->foreign('dia_id')->references('id')->on('dias');
            $table->foreign('trabajo_id')->references('id')->on('trabajos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_trabajos');
    }
};
