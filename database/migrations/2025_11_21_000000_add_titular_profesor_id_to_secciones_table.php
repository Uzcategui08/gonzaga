<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            if (!Schema::hasColumn('secciones', 'titular_profesor_id')) {
                $table->unsignedBigInteger('titular_profesor_id')->nullable()->after('grado_id');
                $table->foreign('titular_profesor_id')
                    ->references('id')
                    ->on('profesores')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            if (Schema::hasColumn('secciones', 'titular_profesor_id')) {
                $table->dropForeign(['titular_profesor_id']);
                $table->dropColumn('titular_profesor_id');
            }
        });
    }
};
