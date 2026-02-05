<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asistencia_extracurricular_estudiante')) {
            return;
        }

        Schema::create('asistencia_extracurricular_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_extracurricular_id')->constrained('asistencias_extracurriculares')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->enum('estado', ['A', 'I', 'P'])->default('A')
                ->comment('A = Asistente, I = Inasistente, P = Pase');
            $table->text('observacion_individual')->nullable();
            $table->timestamps();

            $table->unique(['asistencia_extracurricular_id', 'estudiante_id'], 'asistencia_extra_estudiante_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_extracurricular_estudiante');
    }
};
