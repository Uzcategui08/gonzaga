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
            $table->foreignId('seccion_id')->nullable()->constrained('secciones');

            // Datos personales
            $table->string('codigo_estudiante')->unique()->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('direccion')->nullable();

            // Datos académicos
            $table->enum('estado', ['activo', 'inactivo', 'egresado'])->default('activo')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->text('observaciones')->nullable();

            // Datos de contacto emergencia
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_parentesco')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiantes');
    }
};
