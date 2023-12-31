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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('persona_id')->unique();
            $table->unsignedBigInteger('estado_id');
            $table->string('correo')->unique();
            $table->string('otro_correo')->nullable();
            $table->string('contrasena');
            $table->binary('imagen');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('estado_id')->references('id')->on('estados')->onDelete('cascade');
        });
        DB::statement('ALTER TABLE usuarios MODIFY imagen MEDIUMBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function ($table) {
            $table->dropForeign(['rol_id']);
            $table->dropForeign(['persona_id']);
            $table->dropForeign(['estado_id']);
        });
        Schema::dropIfExists('usuarios');
    }
};
