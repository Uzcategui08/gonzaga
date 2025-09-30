<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the unique constraint exists before trying to drop it
        $constraintExists = DB::select(
            "SELECT constraint_name FROM information_schema.table_constraints 
             WHERE table_name = 'asignaciones' 
             AND constraint_name = 'asignaciones_materia_id_seccion_id_unique' 
             AND table_schema = 'public'"
        );
        
        if (!empty($constraintExists)) {
            Schema::table('asignaciones', function (Blueprint $table) {
                $table->dropUnique(['materia_id', 'seccion_id']);
            });
        }
        // If constraint doesn't exist, the migration goal is already achieved
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->unique(['materia_id', 'seccion_id']);
        });
    }
};