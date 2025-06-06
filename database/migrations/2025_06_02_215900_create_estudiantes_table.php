<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('secciones');

            // Datos personales
            $table->string('codigo_estudiante')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento');
            $table->string('genero');
            $table->string('direccion')->nullable();

            // Datos académicos
            $table->enum('estado', ['activo', 'inactivo', 'egresado'])->default('activo');
            $table->date('fecha_ingreso');
            $table->text('observaciones')->nullable();

            // Datos de contacto emergencia
            $table->string('contacto_emergencia_nombre');
            $table->string('contacto_emergencia_parentesco');
            $table->string('contacto_emergencia_telefono');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiantes');
    }
};
