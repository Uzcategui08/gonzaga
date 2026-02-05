<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asistencias_extracurriculares')) {
            return;
        }

        Schema::create('asistencias_extracurriculares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clase_extracurricular_id')->constrained('clases_extracurriculares')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->text('contenido_clase');
            $table->text('observacion_general')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['clase_extracurricular_id', 'fecha'], 'asistencia_extra_clase_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_extracurriculares');
    }
};
