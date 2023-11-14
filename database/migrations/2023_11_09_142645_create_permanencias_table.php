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
        Schema::create('permanencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fecha_id')->nullable();
            $table->string('nombre');
            $table->foreign('fecha_id')->references('id')->on('fechas')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permanencias');
    }
};
