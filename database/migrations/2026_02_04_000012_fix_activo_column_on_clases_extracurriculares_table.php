<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clases_extracurriculares')) {
            return;
        }

        $hasActivo = Schema::hasColumn('clases_extracurriculares', 'activo');
        $hasActiva = Schema::hasColumn('clases_extracurriculares', 'activa');

        if (!$hasActivo && $hasActiva) {
            DB::statement('ALTER TABLE clases_extracurriculares RENAME COLUMN activa TO activo');
            $hasActivo = true;
            $hasActiva = false;
        }

        if (!$hasActivo && !$hasActiva) {
            DB::statement('ALTER TABLE clases_extracurriculares ADD COLUMN activo boolean NOT NULL DEFAULT true');
        }
    }

    public function down(): void
    {
        // No-op: avoid data-loss or breaking older code paths.
    }
};
