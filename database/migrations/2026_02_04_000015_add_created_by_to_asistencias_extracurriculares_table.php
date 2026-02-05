<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asistencias_extracurriculares')) {
            return;
        }

        Schema::table('asistencias_extracurriculares', function (Blueprint $table) {
            if (!Schema::hasColumn('asistencias_extracurriculares', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('asistencias_extracurriculares')) {
            return;
        }

        Schema::table('asistencias_extracurriculares', function (Blueprint $table) {
            if (Schema::hasColumn('asistencias_extracurriculares', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
