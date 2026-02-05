<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clase_extracurricular_estudiante')) {
            return;
        }

        Schema::create('clase_extracurricular_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clase_extracurricular_id')->constrained('clases_extracurriculares')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['clase_extracurricular_id', 'estudiante_id'], 'clase_extra_estudiante_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clase_extracurricular_estudiante');
    }
};
