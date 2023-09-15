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
        Schema::create('barrios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('id_distrito');
            $table->foreign('id_distrito')->references('id')->on('distritos');
            $table->unsignedBigInteger('id_canton');
            $table->foreign('id_canton')->references('id')->on('cantones');
            $table->unsignedBigInteger('id_provincia');
            $table->foreign('id_provincia')->references('id')->on('provincias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barrios');
    }
};
