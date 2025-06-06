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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo', ['admin', 'profesor'])->default('profesor');
            $table->string('cedula')->unique()->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'cedula', 'fecha_nacimiento', 'direccion', 'telefono']);
        });
    }
};
