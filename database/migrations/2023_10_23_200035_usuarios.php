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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('estado_id');
            $table->string('correo');
            $table->string('otro_correo');
            $table->string('contasena');
            $table->binary('imagen');
            $table->timestamps();
            $table->foreign('rol_id')->references('id')->on('roles');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->foreign('estado_id')->references('id')->on('estados');
        });
        DB::statement('ALTER TABLE usuarios MODIFY imagen MEDIUMBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
