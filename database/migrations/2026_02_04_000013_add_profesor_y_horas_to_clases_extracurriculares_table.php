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
            if (!Schema::hasColumn('clases_extracurriculares', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }

            if (!Schema::hasColumn('clases_extracurriculares', 'hora_inicio')) {
                $table->time('hora_inicio')->nullable();
            }

            if (!Schema::hasColumn('clases_extracurriculares', 'hora_fin')) {
                $table->time('hora_fin')->nullable();
            }

            if (!Schema::hasColumn('clases_extracurriculares', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // No-op: keep backwards compatibility and avoid dropping columns.
    }
};
