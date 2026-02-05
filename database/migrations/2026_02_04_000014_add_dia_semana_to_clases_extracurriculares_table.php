<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clases_extracurriculares')) {
            return;
        }

        Schema::table('clases_extracurriculares', function (Blueprint $table) {
            if (!Schema::hasColumn('clases_extracurriculares', 'dia_semana')) {
                // ISO-8601: 1 = Lunes ... 7 = Domingo
                $table->unsignedTinyInteger('dia_semana')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clases_extracurriculares')) {
            return;
        }

        Schema::table('clases_extracurriculares', function (Blueprint $table) {
            if (Schema::hasColumn('clases_extracurriculares', 'dia_semana')) {
                $table->dropColumn('dia_semana');
            }
        });
    }
};
