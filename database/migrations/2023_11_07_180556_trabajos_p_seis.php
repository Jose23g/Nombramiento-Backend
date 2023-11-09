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
        Schema::create('trabajos_p_seis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_id');
            $table->unsignedBigInteger('p_seis_id');
            $table->foreign('trabajo_id')->references('id')->on('trabajos');
            $table->foreign('p_seis_id')->references('id')->on('p_seis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajos_p_seis');
    }
};
