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
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_propietario_id');
            $table->unsignedBigInteger('usuario_coordinador_id')->nullable();
            $table->unsignedBigInteger('usuario_direccion_id')->nullable();
            $table->unsignedBigInteger('usuario_docencia_id')->nullable();
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->unsignedBigInteger('estado_general_id')->nullable();
            $table->binary('archivo');
            $table->string('observacion')->nullable();
            $table->foreign('usuario_propietario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('usuario_coordinador_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('usuario_direccion_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('usuario_docencia_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('estado_id')->references('id')->on('estados')->onDelete('cascade');
            $table->foreign('estado_general_id')->references('id')->on('estados')->onDelete('cascade');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE archivos MODIFY archivo MEDIUMBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
