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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banco_id')->nullable();
            $table->unsignedBigInteger('distrito_id')->nullable();
            $table->unsignedBigInteger('canton_id')->nullable();
            $table->unsignedBigInteger('provincia_id')->nullable();
            $table->string('otras_senas');
            $table->string('nombre');
            $table->string('cedula');
            $table->string('cuenta');
            $table->foreign('banco_id')->references('id')->on('bancos')->onDelete('set null');
            $table->foreign('distrito_id')->references('id')->on('distritos')->onDelete('set null');
            $table->foreign('canton_id')->references('id')->on('cantones')->onDelete('set null');
            $table->foreign('provincia_id')->references('id')->on('provincias')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
