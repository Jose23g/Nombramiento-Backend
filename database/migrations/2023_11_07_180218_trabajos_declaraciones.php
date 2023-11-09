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
        Schema::create('trabajos_declaraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_id');
            $table->unsignedBigInteger('declaracion_jurada_id');
            $table->foreign('trabajo_id')->references('id')->on('trabajos');
            $table->foreign('declaracion_jurada_id')->references('id')->on('declaraciones_juradas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajos_declaraciones');
    }
};
